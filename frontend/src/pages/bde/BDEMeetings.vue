<template>
  <div class="page-container animate-in">
    <section class="section-block">
      <div class="section-header">
        <span class="section-title">
          Scheduled Meetings
          <span class="section-count">{{ meetings.length }}</span>
        </span>
      </div>
      
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading your schedule...</p>
      </div>

      <div v-else-if="meetings.length === 0" class="empty-state">
        <Video :size="48" style="opacity: 0.5; color: var(--primary)" />
        <p>You have no scheduled meetings at this time.</p>
      </div>

      <div v-else class="meetings-grid">
        <div v-for="meeting in meetings" :key="meeting.id" class="meeting-card glass-card">
          <div class="meeting-info">
            <h3 class="meeting-title">{{ meeting.title }}</h3>
            <p class="meeting-date"><Calendar :size="14" /> {{ formatDateTime(meeting.date) }}</p>
            <p v-if="meeting.pdf_path" class="meeting-report" title="View automatic report">
              <a :href="'/storage/' + meeting.pdf_path" target="_blank">
                <FileText :size="14" /> View Associated Case Report
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

    <section class="section-block">
      <div class="section-header">
        <span class="section-title">
          Your Escalated Reports
          <span class="section-count">{{ reports.length }}</span>
        </span>
      </div>
      
      <div v-if="reportsLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading reports...</p>
      </div>

      <div v-else-if="reports.length === 0" class="empty-state">
        <FileText :size="48" style="opacity: 0.5; color: var(--primary)" />
        <p>No reports have been escalated to meetings yet.</p>
      </div>

      <div v-else class="reports-grid">
        <report-card 
          v-for="report in reports"
          :key="report.id"
          :title="report.message?.substring(0, 60) || 'Escalated Report'"
          :category="getCategory(report)"
          :priority="report.priority || 'P2'"
          :status="formatStatus(report.status)"
          :description="report.message || 'No description available.'"
        >
          <div class="report-meta">
            <span class="meta-item"><Calendar :size="12" /> Created on {{ formatDate(report.created_at) }}</span>
          </div>
        </report-card>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useBDEReportsStore } from '../../stores/bdeReports';
import api from '../../services/api';
import ReportCard from '../../components/ReportCard.vue';
import { Video, FileText, Calendar } from 'lucide-vue-next';

const bdeStore = useBDEReportsStore();

const meetings = ref([]);
const reports = ref([]);
const loading = ref(false);
const reportsLoading = ref(false);

onMounted(async () => {
  await Promise.all([fetchMeetings(), fetchReports()]);
});

const fetchMeetings = async () => {
  loading.value = true;
  try {
    const response = await api.get('/request-meetings');
    const data = response.data?.data || [];
    
    meetings.value = data
      .filter(rm => rm.status === 'approved' && rm.meeting)
      .map(rm => ({
        id: rm.meeting.id,
        title: rm.meeting.title,
        date: rm.meeting.date,
        link: rm.meeting.link,
        pdf_path: rm.meeting.pdf_path
      }));
  } catch (error) {
    console.error('Error fetching meetings:', error);
  } finally {
    loading.value = false;
  }
};

const fetchReports = async () => {
  reportsLoading.value = true;
  try {
    const data = await bdeStore.fetchReports();
    reports.value = data.data || [];
  } catch (error) {
    console.error('Error fetching reports:', error);
  } finally {
    reportsLoading.value = false;
  }
};

const getCategory = (report) => (report.reports && report.reports.length > 0) ? report.reports[0].category?.name || 'General' : 'General';
const formatStatus = (s) => ({ pending: 'Pending', resolved: 'Resolved', rejected: 'Rejected', escalated: 'Escalated' }[s?.toLowerCase()] || s);
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
const formatDateTime = (date) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<style scoped>
/* Scoped Layout adjustments for inner router-view */
.page-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Sections */
.section-block {
  margin-bottom: 2rem;
}
.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.25rem;
}
.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.section-count {
  font-size: 0.75rem;
  background: var(--surface-hover);
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-weight: 600;
}

/* Grids */
.meetings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1.5rem;
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}

/* Meeting Cards */
.meeting-card {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 1.5rem;
  border-left: 4px solid var(--primary);
  background: rgba(30, 41, 59, 0.8);
  border-radius: var(--radius-md);
  border-top: 1px solid rgba(255,255,255,0.1);
  border-right: 1px solid rgba(255,255,255,0.1);
  border-bottom: 1px solid rgba(255,255,255,0.1);
  gap: 1rem;
}

.meeting-title {
  font-size: 1.0625rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: var(--text-main);
}

.meeting-date {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  color: var(--text-muted);
  font-size: 0.8125rem;
  margin: 0 0 0.5rem;
}

.meeting-report a {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  color: var(--primary-light);
  font-size: 0.8125rem;
  font-weight: 500;
  text-decoration: none;
  transition: color var(--transition-fast);
}
.meeting-report a:hover {
  color: var(--primary);
}

.meeting-actions {
  display: flex;
  justify-content: flex-start;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all var(--transition-fast);
  text-decoration: none;
}
.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
  padding: 0.6rem 1rem;
  font-size: 0.875rem;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px var(--primary-glow);
}

/* Empty / Loading */
.empty-state, .loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.report-meta {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: var(--text-muted);
}
.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

@media (max-width: 640px) {
  .meetings-grid, .reports-grid { grid-template-columns: 1fr; }
}
</style>
