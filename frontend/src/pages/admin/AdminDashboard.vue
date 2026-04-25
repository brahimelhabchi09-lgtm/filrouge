<template>
    <!-- Summary stat chips row -->
    <div class="stats-grid">
      <div class="stat-card" style="--delay:0s">
        <div class="stat-icon" style="background:var(--p3-bg);color:var(--p3)"><Users :size="22" /></div>
        <div><p class="stat-label">Total Users</p><p class="stat-value">{{ users.length }}</p></div>
      </div>
      <div class="stat-card" style="--delay:0.07s">
        <div class="stat-icon" style="background:var(--p2-bg);color:var(--p2)"><FileText :size="22" /></div>
        <div><p class="stat-label">Total Reports</p><p class="stat-value">{{ reportsPagination.total }}</p></div>
      </div>
      <div class="stat-card" style="--delay:0.14s">
        <div class="stat-icon" style="background:var(--success-bg);color:var(--success)"><Video :size="22" /></div>
        <div><p class="stat-label">Meetings</p><p class="stat-value">{{ meetings.length }}</p></div>
      </div>
      <div class="stat-card" style="--delay:0.21s">
        <div class="stat-icon" style="background:var(--p1-bg);color:var(--p1)"><Clock :size="22" /></div>
        <div><p class="stat-label">Pending Requests</p><p class="stat-value">{{ requestPagination.total }}</p></div>
      </div>
    </div>

    <!-- ── All Reports Section ─────────────────── -->
    <section class="section-block">
      <div class="section-header">
        <span class="section-title">
          All Reports
          <span class="section-count">{{ reportsPagination.total }}</span>
        </span>
      </div>
      <div v-if="reportsLoading" class="loading-state"><div class="spinner"></div><p>Loading…</p></div>
      <div v-else-if="reports.length === 0" class="empty-state"><FileText :size="36" /><p>No reports found.</p></div>
      <div v-else class="reports-grid">
        <report-card
          v-for="report in reports"
          :key="report.id"
          :title="report.message?.substring(0, 60) || 'Report'"
          :category="report.category?.name || 'General'"
          :priority="report.priority || 'P2'"
          :status="formatStatus(report.status)"
          :description="report.message || 'No description'"
          :affected="`Student: ${report.student?.first_name || ''} ${report.student?.last_name || ''}`"
        >
          <span class="report-date">{{ formatDate(report.created_at) }}</span>
        </report-card>
      </div>
      <pagination
        v-if="reportsPagination.total > reportsPagination.perPage"
        :current-page="reportsPagination.currentPage"
        :total-pages="Math.ceil(reportsPagination.total / reportsPagination.perPage)"
        @page-change="fetchReports"
      />
    </section>

    <!-- ── User Management Section ────────────── -->
    <section class="section-block">
      <div class="section-header">
        <span class="section-title">
          User Management
          <span class="section-count">{{ users.length }}</span>
        </span>
        <router-link :to="{ name: 'admin-create-user' }" class="btn btn-primary">
          <UserPlus :size="15" /> Add User
        </router-link>
      </div>
      <div class="glass-card table-card">
        <div class="table-container">
          <table v-if="users.length">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th style="text-align:right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" class="table-row">
                <td>
                  <div class="user-cell">
                    <div class="user-avatar">{{ userInitials(user) }}</div>
                    <div>
                      <div class="user-name">{{ user.first_name }} {{ user.last_name }}</div>
                      <div class="user-email">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td><span class="role-badge" :class="`role-${user.role?.toLowerCase()}`">{{ capitalize(user.role) }}</span></td>
                <td><span class="status-active"><span class="dot"></span>Active</span></td>
                <td class="muted-cell">{{ formatDate(user.created_at) }}</td>
                <td style="text-align:right">
                  <button class="btn-icon"><Edit2 :size="13" /></button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else-if="!loading" class="empty-state"><Users :size="36" /><p>No users found.</p></div>
          <div v-else class="loading-state"><div class="spinner"></div></div>
        </div>
        <pagination
          v-if="userPagination.total > userPagination.perPage"
          :current-page="userPagination.currentPage"
          :total-pages="Math.ceil(userPagination.total / userPagination.perPage)"
          @page-change="fetchUsers"
        />
      </div>
    </section>

    <!-- ── Meeting Requests Section ───────────── -->
    <section class="section-block">
      <div class="section-header">
        <span class="section-title">
          Meeting Requests
          <span class="section-count">{{ requestPagination.total }}</span>
        </span>
      </div>
      <div v-if="loading" class="loading-state"><div class="spinner"></div><p>Loading…</p></div>
      <div v-else-if="requestMeetings.length === 0" class="empty-state"><CalendarOff :size="36" /><p>No pending meeting requests.</p></div>
      <div v-else class="requests-list">
        <div v-for="request in requestMeetings" :key="request.id" class="request-card glass-card">
          <div class="request-header">
            <div class="request-left">
              <span class="request-badge">Meeting Request #{{ request.id }}</span>
              <h3 class="request-title">{{ request.generated_report?.message?.substring(0, 80) || 'Meeting Request' }}</h3>
            </div>
            <div class="request-priority">
              <span class="priority-chip" :class="`pchip-${(request.generated_report?.priority || 'P2').toUpperCase()}`">
                {{ request.generated_report?.priority || 'P2' }}
              </span>
            </div>
          </div>

          <div class="request-meta-grid">
            <div class="meta-box">
              <span class="meta-label">BDE Member</span>
              <span class="meta-value">{{ request.bde?.first_name }} {{ request.bde?.last_name }}</span>
            </div>
            <div class="meta-box">
              <span class="meta-label">Proposed Date</span>
              <span class="meta-value">{{ formatDateTime(request.meeting_date) }}</span>
            </div>
            <div class="meta-box">
              <span class="meta-label">Reports Grouped</span>
              <span class="meta-value">{{ request.generated_report?.reports_count || 1 }} report(s)</span>
            </div>
            <div class="meta-box">
              <span class="meta-label">Requested On</span>
              <span class="meta-value">{{ formatDate(request.created_at) }}</span>
            </div>
          </div>

          <div v-if="request.notes" class="request-notes">
            <span class="meta-label">BDE Notes</span>
            <p>{{ request.notes }}</p>
          </div>

          <div class="request-actions">
            <button class="btn btn-success btn-sm" @click="openScheduleModal(request)">
              <CheckCircle :size="14" /> Accept & Schedule
            </button>
            <button class="btn btn-danger btn-sm" @click="openRefusalModal(request)">
              <XCircle :size="14" /> Reject
            </button>
          </div>
        </div>
      </div>
      <pagination
        v-if="requestPagination.total > requestPagination.perPage"
        :current-page="requestPagination.currentPage"
        :total-pages="Math.ceil(requestPagination.total / requestPagination.perPage)"
        @page-change="fetchRequestMeetings"
      />
    </section>

    <!-- ── Scheduled Meetings Section ────────── -->
    <section v-if="meetings.length > 0" class="section-block">
      <div class="section-header">
        <span class="section-title">Scheduled Meetings <span class="section-count">{{ meetings.length }}</span></span>
      </div>
      <div class="meetings-list">
        <div v-for="meeting in meetings" :key="meeting.id" class="meeting-item glass-card">
          <div class="meeting-info">
            <h3>{{ meeting.title }}</h3>
            <p><Calendar :size="12" /> {{ formatDateTime(meeting.date) }}</p>
          </div>
          <div class="meeting-right">
            <a v-if="meeting.pdf_path" :href="`http://localhost:3000/storage/${meeting.pdf_path}`" target="_blank" class="pdf-link">
              <FileText :size="14" /> View PDF
            </a>
            <a :href="meeting.link" target="_blank" class="join-link">
              <Video :size="14" /> Join
            </a>
            <span class="badge-success-pill">Scheduled</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Refusal Modal -->
    <refusal-modal
      :is-open="showRefusalModal"
      @close="showRefusalModal = false"
      @submit="handleRefusal"
    />

    <!-- Schedule Modal -->
    <div v-if="showScheduleModal" class="modal-overlay" @click.self="showScheduleModal = false">
      <div class="modal-content glass-card">
        <h3>Schedule Meeting</h3>
        <p class="org-note">Create meeting from YOUPORTS organization email</p>
        <form @submit.prevent="submitMeeting">
          <div class="form-group">
            <label>Meeting Title</label>
            <input v-model="meetingForm.title" type="text" class="form-control" placeholder="E.g. Meeting with BDE – Infrastructure" required />
          </div>
          <div class="form-group">
            <label>Date &amp; Time</label>
            <input v-model="meetingForm.date" type="datetime-local" class="form-control" required :min="minDate" />
          </div>
          <div class="form-group">
            <label>Meeting Link <span class="label-note">(leave empty for auto Google Meet link)</span></label>
            <input v-model="meetingForm.link" type="text" class="form-control" placeholder="Optional meeting URL" />
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="showScheduleModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner spinner-sm"></span>
              <span v-else>Schedule Meeting</span>
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAdminStore } from '../../stores/admin';
import ReportCard from '../../components/ReportCard.vue';
import RefusalModal from '../../components/RefusalModal.vue';
import Pagination from '../../components/Pagination.vue';
import { Users, FileText, Video, Clock, Edit2, CalendarOff, Calendar, UserPlus, CheckCircle, XCircle } from 'lucide-vue-next';

const adminStore = useAdminStore();
const { users, reports, meetings, requestMeetings, userMeta, reportsMeta, requestMeta } = storeToRefs(adminStore);

const userPagination = computed(() => ({ currentPage: userMeta.value.current_page, perPage: userMeta.value.per_page, total: userMeta.value.total }));
const reportsPagination = computed(() => ({ currentPage: reportsMeta.value.current_page, perPage: reportsMeta.value.per_page, total: reportsMeta.value.total }));
const requestPagination = computed(() => ({ currentPage: requestMeta.value.current_page, perPage: requestMeta.value.per_page, total: requestMeta.value.total }));

const loading = ref(false);
const reportsLoading = ref(false);
const showRefusalModal = ref(false);
const showScheduleModal = ref(false);
const submitting = ref(false);
const selectedRequest = ref(null);
const meetingForm = reactive({ title: '', date: '', link: '' });

const minDate = computed(() => {
  const d = new Date(); d.setDate(d.getDate() + 1);
  return d.toISOString().slice(0, 16);
});

onMounted(async () => {
  loading.value = true;
  try {
    await Promise.allSettled([
      adminStore.fetchUsers(),
      adminStore.fetchRequestMeetings(),
      adminStore.fetchMeetings(),
      fetchReports(),
    ]);
  } finally {
    loading.value = false;
  }
});

const fetchUsers = (page = 1) => adminStore.fetchUsers(page);
const fetchRequestMeetings = (page = 1) => adminStore.fetchRequestMeetings(page);
const fetchMeetings = () => adminStore.fetchMeetings();

const fetchReports = async (page = 1) => {
  reportsLoading.value = true;
  try {
    await adminStore.fetchReports(page);
  } catch (e) { console.error(e); }
  finally { reportsLoading.value = false; }
};

const formatStatus = (status) => ({ pending: 'Pending', resolved: 'Resolved', rejected: 'Rejected', escalated: 'Escalated' }[status?.toLowerCase()] || status);
const userInitials = (u) => ((u.first_name || '')[0] + (u.last_name || '')[0]).toUpperCase();
const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1).toLowerCase() : '';
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';

const openRefusalModal = (req) => { selectedRequest.value = req; showRefusalModal.value = true; };
const openScheduleModal = (req) => {
  selectedRequest.value = req;
  meetingForm.title = `Meeting – ${(req.generated_report?.message || 'Report').substring(0, 30)}...`;
  meetingForm.date = ''; meetingForm.link = '';
  showScheduleModal.value = true;
};

const handleRefusal = async (reason) => {
  if (!selectedRequest.value) return;
  try {
    await adminStore.rejectRequestMeeting(selectedRequest.value.id, reason);
    showRefusalModal.value = false;
    await adminStore.fetchRequestMeetings();
  } catch (e) { console.error(e); }
};

const submitMeeting = async () => {
  if (!selectedRequest.value) return;
  submitting.value = true;
  try {
    await adminStore.createMeeting({ request_meeting_id: selectedRequest.value.id, title: meetingForm.title, date: meetingForm.date, link: meetingForm.link.trim() || undefined });
    showScheduleModal.value = false;
    meetingForm.title = ''; meetingForm.date = ''; meetingForm.link = '';
    await Promise.all([adminStore.fetchRequestMeetings(), adminStore.fetchMeetings()]);
  } catch (e) { alert(e.response?.data?.message || 'Failed to create meeting'); }
  finally { submitting.value = false; }
};
</script>

<style scoped>
/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

/* Section blocks */
.section-block { margin-bottom: 2.5rem; }

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

/* Reports grid */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

/* Table */
.table-card { overflow: hidden; }

.table-container { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; }

thead { background: var(--surface-hover); border-bottom: 1px solid var(--glass-border); }

th {
  padding: 0.875rem 1rem;
  text-align: left;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  white-space: nowrap;
}

.table-row td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--glass-border); }

.table-row:last-child td { border-bottom: none; }

.table-row:hover td { background: var(--glass); }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }

.user-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.6875rem; font-weight: 700; color: white; flex-shrink: 0;
}

.user-name { font-weight: 600; font-size: 0.875rem; color: var(--text-main); }
.user-email { font-size: 0.75rem; color: var(--text-muted); }
.muted-cell { font-size: 0.8125rem; color: var(--text-muted); }

/* Role badges */
.role-badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  border-radius: var(--radius-full);
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border: 1px solid transparent;
}

.role-admin { background: var(--role-admin-bg); color: var(--role-admin); border-color: rgba(99,102,241,0.2); }
.role-bde { background: var(--role-bde-bg); color: var(--role-bde); border-color: rgba(139,92,246,0.2); }
.role-teacher { background: var(--role-teacher-bg); color: var(--role-teacher); border-color: rgba(16,185,129,0.2); }
.role-student { background: var(--role-student-bg); color: var(--role-student); border-color: rgba(56,189,248,0.2); }

.status-active {
  display: inline-flex; align-items: center; gap: 0.375rem;
  font-size: 0.8125rem; color: var(--success);
}

.dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--success); box-shadow: 0 0 5px var(--success);
}

.btn-icon {
  width: 28px; height: 28px;
  display: inline-flex; align-items: center; justify-content: center;
  background: transparent; border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm); color: var(--text-muted); cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-icon:hover { background: var(--surface-hover); color: var(--text-main); }

/* Request meeting cards */
.requests-list { display: flex; flex-direction: column; gap: 1rem; }

.request-card {
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  border-left: 3px solid var(--primary);
  transition: all var(--transition-normal);
}

.request-card:hover { border-left-color: var(--accent); }

.request-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.request-left { flex: 1; min-width: 0; }
.request-badge {
  font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--primary-light); background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.2);
  padding: 0.15rem 0.5rem; border-radius: var(--radius-full); display: inline-block; margin-bottom: 0.375rem;
}
.request-title { font-size: 0.9375rem; font-weight: 600; color: var(--text-main); line-height: 1.4; }

.priority-chip {
  font-size: 0.625rem; font-weight: 800; letter-spacing: 0.1em;
  padding: 0.25rem 0.625rem; border-radius: var(--radius-full); border: 1px solid transparent; flex-shrink: 0;
}
.pchip-P0 { background: var(--p0-bg); color: var(--p0); border-color: rgba(244,63,94,0.3); }
.pchip-P1 { background: var(--p1-bg); color: var(--p1); border-color: rgba(245,158,11,0.3); }
.pchip-P2 { background: var(--p2-bg); color: var(--p2); border-color: rgba(6,182,212,0.3); }
.pchip-P3 { background: var(--p3-bg); color: var(--p3); border-color: rgba(124,58,237,0.3); }

.request-meta-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;
}

.meta-box {
  background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);
  border-radius: var(--radius-md); padding: 0.625rem 0.75rem;
}

.meta-label { display: block; font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: 0.25rem; }
.meta-value { font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); }

.request-notes {
  background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.15);
  border-radius: var(--radius-md); padding: 0.75rem 1rem;
}
.request-notes p { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.25rem; line-height: 1.5; }

.request-actions { display: flex; gap: 0.5rem; }

.pdf-link {
  display: inline-flex; align-items: center; gap: 0.375rem;
  color: var(--accent); font-size: 0.8125rem; font-weight: 600;
  padding: 0.35rem 0.75rem; border: 1px solid rgba(6,182,212,0.3);
  border-radius: var(--radius-md); transition: all var(--transition-fast);
  background: rgba(6,182,212,0.05);
}
.pdf-link:hover { background: rgba(6,182,212,0.12); border-color: var(--accent); }

/* Meeting items */
.meetings-list { display: flex; flex-direction: column; gap: 0.75rem; }

.meeting-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.25rem;
}

.meeting-info h3 { font-size: 0.9375rem; font-weight: 600; margin: 0 0 0.25rem; }
.meeting-info p { font-size: 0.8125rem; color: var(--text-muted); margin: 0; }

.meeting-right { display: flex; align-items: center; gap: 0.75rem; }

.join-link {
  display: inline-flex; align-items: center; gap: 0.375rem;
  color: var(--primary-light); font-size: 0.8125rem; font-weight: 500;
  transition: color var(--transition-fast);
}

.join-link:hover { color: var(--primary); }

.badge-success-pill {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  border-radius: var(--radius-full);
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
  background: var(--success-bg); color: var(--success);
  border: 1px solid rgba(16,185,129,0.2);
}

/* Meeting request card additions */
.meeting-meta {
  display: flex; align-items: center; gap: 0.375rem;
  font-size: 0.75rem; color: var(--text-muted);
}

.meeting-date-chip {
  display: inline-flex; align-items: center; gap: 0.25rem;
  background: var(--glass); border: 1px solid var(--glass-border);
  border-radius: var(--radius-full); padding: 0.15rem 0.5rem;
}

.action-btns { display: flex; gap: 0.375rem; flex-shrink: 0; }

/* Shared buttons */
.btn {
  display: inline-flex; align-items: center; gap: 0.375rem;
  padding: 0.5rem 1rem; border-radius: var(--radius-md);
  font-weight: 600; font-size: 0.8125rem; font-family: inherit;
  cursor: pointer; border: none; text-decoration: none;
  transition: all var(--transition-fast);
}

.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white; box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-primary:hover:not(:disabled) { transform: translateY(-1px); }

.btn-success { background: var(--success); color: white; }
.btn-success:hover { background: #059669; }

.btn-danger { background: var(--error); color: white; }
.btn-danger:hover { background: #dc2626; }

.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--glass-border); }
.btn-ghost:hover { background: var(--surface-hover); color: var(--text-main); }

.btn-sm { padding: 0.35rem 0.625rem; font-size: 0.75rem; }

.report-date { font-size: 0.75rem; color: var(--text-muted); }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; z-index: 200;
  animation: fade-in 0.2s ease;
}

.modal-content {
  max-width: 480px; width: calc(100% - 2rem);
  padding: 2rem; animation: slide-up 0.25s ease;
}

.modal-content h3 { font-size: 1.125rem; font-weight: 700; margin: 0 0 0.25rem; }

.org-note { color: var(--text-muted); font-size: 0.8125rem; margin: 0 0 1.5rem; }

.form-group { margin-bottom: 1.25rem; }

.form-group label {
  display: block; margin-bottom: 0.375rem;
  font-weight: 600; font-size: 0.8125rem; color: var(--text-secondary);
}

.label-note { font-weight: 400; color: var(--text-muted); font-size: 0.75rem; }

.form-control {
  width: 100%; background: var(--background);
  border: 1px solid var(--glass-border); border-radius: var(--radius-md);
  padding: 0.7rem 0.875rem; color: var(--text-main); font-family: inherit;
  font-size: 0.9rem;
}

.form-control:focus { outline: none; border-color: var(--primary); box-shadow: var(--ring-glow); }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }

/* Loading */
.loading-state {
  text-align: center; padding: 3rem 2rem;
  color: var(--text-muted); display: flex;
  flex-direction: column; align-items: center; gap: 1rem;
}

/* Responsive */
@media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 540px) {
  .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
  .reports-grid { grid-template-columns: 1fr; }
}
</style>
