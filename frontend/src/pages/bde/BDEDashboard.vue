<template>
    <div v-if="error" class="alert alert-error animate-in">
      <span>{{ error }}</span>
      <button @click="error = null" class="btn-close"><X :size="16" /></button>
    </div>

    <!-- Meetings Section -->
    <section v-if="meetings.length > 0" class="section-block meetings-section animate-in">
      <div class="section-header">
        <span class="section-title">
          Scheduled Meetings
          <span class="section-count">{{ meetings.length }}</span>
        </span>
      </div>
      <div class="meetings-list">
        <div v-for="meeting in meetings" :key="meeting.id" class="meeting-card glass-card">
          <div class="meeting-info">
            <h3 class="meeting-title">{{ meeting.title }}</h3>
            <p class="meeting-date"><Calendar :size="14" /> {{ formatDateTime(meeting.date) }}</p>
            <p v-if="meeting.pdf_path" class="meeting-report" title="View associated report">
              <a :href="'/storage/' + meeting.pdf_path" target="_blank">
                <FileText :size="14" /> View Auto-Generated Summary PDF
              </a>
            </p>
          </div>
          <div class="meeting-actions">
            <a :href="meeting.link" target="_blank" class="btn btn-primary">
              <Video :size="15" />
              <span>Join Meeting</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Reports Section -->
    <section class="section-block pending-reports-section animate-in">
      <div class="section-header">
        <span class="section-title">
          Pending Reports
          <span class="section-count">{{ reports.length }}</span>
        </span>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading reports...</p>
      </div>

      <div v-else-if="reports.length === 0 && !error" class="empty-state">
        <Inbox :size="48" style="opacity: 0.5; color: var(--primary)" />
        <p>No pending reports to review.</p>
      </div>

      <div v-else class="reports-grid">
        <report-card 
          v-for="report in reports"
          :key="report.id"
          :title="report.message?.substring(0, 60) || 'Action Required Report'"
          :category="getCategory(report)"
          :priority="report.priority || 'P2'"
          :status="formatStatus(report.status)"
          :description="report.message || 'No description available.'"
          :affected="`${report.reports_count || 0} overlapping reports`"
        >
          <div class="report-meta">
            <span class="meta-item"><Calendar :size="12" /> {{ formatDate(report.created_at) }}</span>
          </div>
          
          <div class="action-buttons">
            <template v-if="hasMeetingRequest(report)">
              <div 
                class="status-chip" 
                :class="getMeetingStatus(report) === 'approved' ? 'chip-success' : 'chip-warning'"
              >
                <Check v-if="getMeetingStatus(report) === 'approved'" :size="14" />
                <Clock v-else :size="14" />
                {{ getMeetingStatus(report) === 'approved' ? 'Meeting Scheduled' : 'Request Pending Approval' }}
              </div>
            </template>
            <template v-else>
              <button class="btn btn-primary btn-sm" @click="openMeetingModal(report)">
                <CalendarPlus :size="14" />
                <span>Request Meeting</span>
              </button>
              <button class="btn btn-danger btn-sm" @click="openRefusalModal(report)">
                <XCircle :size="14" />
                <span>Refuse</span>
              </button>
            </template>
          </div>
        </report-card>
      </div>
    </section>

    <!-- Modals -->
    <refusal-modal 
      :is-open="showRefusalModal"
      @close="showRefusalModal = false"
      @submit="handleRefusal"
    />

    <div v-if="showMeetingModal" class="modal-overlay" @click.self="showMeetingModal = false">
      <div class="modal-content glass-card">
        <h3>Request Administrator Meeting</h3>
        <p class="modal-subtitle">Propose a meeting with an admin to escalate this report.</p>
        
        <form @submit.prevent="submitMeetingRequest">
          <div class="form-group">
            <label>Proposed Date & Time</label>
            <input 
              v-model="meetingForm.meeting_date"
              type="datetime-local" 
              class="form-control" 
              required
              :min="minDate"
            />
          </div>
          
          <div class="form-group">
            <label>Meeting Link <span class="label-note">(Optional)</span></label>
            <input 
              v-model="meetingForm.meeting_link"
              type="text" 
              class="form-control" 
              placeholder="e.g. meet.google.com/xxx-xxxx-xxx"
            />
            <small class="help-text">Leave blank to let the admin generate a Google Meet link automatically.</small>
          </div>
          
          <div class="form-group">
            <label>Notes & Justification <span class="label-note">(Optional)</span></label>
            <textarea 
              v-model="meetingForm.notes"
              class="form-control textarea" 
              placeholder="Provide context on why this requires an admin meeting..."
            ></textarea>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="showMeetingModal = false">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-sm"></span>
              <template v-else>
                <span>Submit Request</span>
                <Send :size="14" />
              </template>
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useBDEReportsStore } from '../../stores/bdeReports';
import api from '../../services/api';
import ReportCard from '../../components/ReportCard.vue';
import RefusalModal from '../../components/RefusalModal.vue';
import { Calendar, Video, FileText, CalendarPlus, XCircle, Inbox, X, Check, Clock, Send } from 'lucide-vue-next';

const bdeStore = useBDEReportsStore();
const { reports } = storeToRefs(bdeStore);

const meetings = ref([]);
const loading = ref(false);
const error = ref(null);
const showRefusalModal = ref(false);
const showMeetingModal = ref(false);
const selectedReport = ref(null);
const submitting = ref(false);

const meetingForm = reactive({ meeting_date: '', notes: '', meeting_link: '' });

const minDate = computed(() => {
  const t = new Date(); t.setDate(t.getDate() + 1);
  return t.toISOString().slice(0, 16);
});

onMounted(async () => {
  loading.value = true;
  error.value = null;
  try {
    await Promise.all([bdeStore.fetchReports(), fetchMeetings()]);
  } catch (err) {
    error.value = err.message || 'Failed to load data';
  } finally {
    loading.value = false;
  }
});

const fetchReports = async (page = 1) => {
  loading.value = true;
  try {
    await bdeStore.fetchReports(page);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const fetchMeetings = async () => {
  try {
    const res = await api.get('/request-meetings');
    const d = res.data?.data || [];
    meetings.value = d
      .filter(rm => rm.status === 'approved' && rm.meeting)
      .map(rm => ({ id: rm.meeting.id, title: rm.meeting.title, date: rm.meeting.date, link: rm.meeting.link, pdf_path: rm.meeting.pdf_path }));
  } catch (err) {
    console.error('Error fetching meetings:', err);
  }
};

const getCategory = (report) => (report.reports?.length > 0) ? report.reports[0].category?.name || 'General' : 'General';
const hasMeetingRequest = (report) => report.request_meeting && ['pending', 'approved'].includes(report.request_meeting.status);
const getMeetingStatus = (report) => report.request_meeting?.status || null;
const formatStatus = (s) => ({ pending: 'Pending', approved: 'Approved', rejected: 'Rejected', escalated: 'Escalated' }[s?.toLowerCase()] || s);
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
const formatDateTime = (d) => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const openMeetingModal = (report) => {
  selectedReport.value = report;
  meetingForm.meeting_date = ''; meetingForm.notes = ''; meetingForm.meeting_link = '';
  showMeetingModal.value = true;
};
const openRefusalModal = (report) => { selectedReport.value = report; showRefusalModal.value = true; };

const submitMeetingRequest = async () => {
  if (!selectedReport.value) return;
  submitting.value = true; error.value = null;
  try {
    await bdeStore.approveReport(selectedReport.value.id, {
      meeting_date: meetingForm.meeting_date, notes: meetingForm.notes, meeting_link: meetingForm.meeting_link || undefined
    });
    showMeetingModal.value = false;
    await bdeStore.fetchReports();
    await fetchMeetings();
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to submit request';
  } finally { submitting.value = false; }
};

const handleRefusal = async (reason) => {
  if (!selectedReport.value) return;
  submitting.value = true; error.value = null;
  try {
    await bdeStore.denyReport(selectedReport.value.id, reason);
    showRefusalModal.value = false;
    await bdeStore.fetchReports();
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to refuse report';
  } finally { submitting.value = false; }
};
</script>

<style scoped>
/* Sections */
.section-block { margin-bottom: 3rem; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.section-title { font-size: 1.25rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.75rem; }
.section-count { font-size: 0.75rem; background: var(--surface-hover); padding: 0.15rem 0.6rem; border-radius: var(--radius-full); color: var(--text-secondary); font-weight: 600; }

/* Alert */
.alert { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; font-size: 0.8125rem; font-weight: 500; }
.alert-error { background: var(--error-bg); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--error); }
.btn-close { background: transparent; border: none; color: inherit; cursor: pointer; padding: 0.25rem; border-radius: var(--radius-sm); transition: background 0.2s; }
.btn-close:hover { background: rgba(255,255,255,0.1); }

/* Meetings */
.meetings-list { display: flex; flex-direction: column; gap: 1rem; }
.meeting-card { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-left: 4px solid var(--primary); }
.meeting-title { font-size: 1.0625rem; font-weight: 600; margin: 0 0 0.5rem; color: var(--text-main); }
.meeting-date { display: inline-flex; align-items: center; gap: 0.375rem; color: var(--text-muted); font-size: 0.8125rem; margin: 0 0 0.5rem; }
.meeting-report a { display: inline-flex; align-items: center; gap: 0.375rem; color: var(--primary-light); font-size: 0.8125rem; font-weight: 500; text-decoration: none; transition: color var(--transition-fast); }
.meeting-report a:hover { color: var(--primary); }

/* Reports */
.reports-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem; }
.report-meta { margin-top: 0.5rem; font-size: 0.8125rem; color: var(--text-muted); }
.meta-item { display: inline-flex; align-items: center; gap: 0.375rem; }

.action-buttons { display: flex; gap: 0.5rem; margin-top: 1.25rem; }
.btn { display: inline-flex; align-items: center; gap: 0.375rem; border-radius: var(--radius-md); font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all var(--transition-fast); text-decoration: none;}
.btn-sm { padding: 0.45rem 0.875rem; font-size: 0.8125rem; }
.btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: white; box-shadow: 0 4px 12px var(--primary-glow); }
.btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 18px var(--primary-glow); }
.btn-danger { background: var(--error); color: white; }
.btn-danger:hover { background: #dc2626; }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid transparent; }
.btn-ghost:hover { background: var(--surface-hover); color: var(--text-main); }

/* Chips */
.status-chip { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.4rem 0.875rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid transparent; }
.chip-success { background: var(--success-bg); color: var(--success); border-color: rgba(16,185,129,0.2); }
.chip-warning { background: var(--p1-bg); color: var(--p1); border-color: rgba(245,158,11,0.2); }

/* Empty / Loading */
.empty-state, .loading-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); display: flex; flex-direction: column; align-items: center; gap: 1rem; }

/* Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 200; animation: fade-in 0.2s ease; }
.modal-content { max-width: 480px; width: calc(100% - 2rem); padding: 2.25rem; animation: slide-up 0.25s ease; }
.modal-content h3 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem 0; color: var(--text-main); }
.modal-subtitle { font-size: 0.875rem; color: var(--text-muted); margin: 0 0 1.5rem 0; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8125rem; }
.label-note { font-weight: 400; color: var(--text-muted); font-size: 0.75rem; }
.form-control { width: 100%; background: var(--background); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 0.75rem 1rem; color: var(--text-main); font-family: inherit; font-size: 0.875rem; }
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: var(--ring-glow); }
.textarea { resize: vertical; min-height: 90px; }
.help-text { display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 0.375rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; }

@media (max-width: 640px) {
  .reports-grid { grid-template-columns: 1fr; }
  .meeting-card { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
</style>
