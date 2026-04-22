<template>
  <div class="report-card" :class="`priority-${priority?.toUpperCase()}`">
    <div class="card-header">
      <span class="category-pill">{{ category }}</span>
      <div class="header-right">
        <div class="status-badge" :class="statusClass">
          <span class="status-dot"></span>
          {{ status }}
        </div>
        <priority-badge :priority="priority" />
      </div>
    </div>

    <div class="card-body">
      <h3 class="card-title">{{ title }}</h3>
      <p class="card-description">{{ description }}</p>
    </div>

    <div v-if="affected" class="card-meta">
      <Users :size="13" />
      <span>{{ affected }}</span>
    </div>

    <div class="card-footer">
      <div class="card-actions">
        <slot></slot>
      </div>
    </div>

    <div v-if="status === 'Refused' && refusalReason" class="refusal-section">
      <div class="refusal-header">
        <AlertCircle :size="13" />
        <span>Rejection Reason</span>
      </div>
      <p class="refusal-text">{{ refusalReason }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import PriorityBadge from './PriorityBadge.vue';
import { Users, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
  title: String,
  category: String,
  priority: String,
  status: String,
  description: String,
  refusalReason: String,
  affected: String
});

const statusClass = computed(() => {
  return `status-${props.status?.toLowerCase().replace(/\s/g, '-')}`;
});
</script>

<style scoped>
.report-card {
  background: var(--surface-elevated);
  border: 1px solid var(--glass-border);
  border-left: 4px solid var(--glass-border);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.25rem 1.25rem 1.125rem;
  transition: all var(--transition-normal);
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  box-shadow: var(--shadow-card);
  animation: slide-up 0.4s ease both;
}

/* Left-border priority accent */
.priority-P0 { border-left-color: var(--p0); }
.priority-P1 { border-left-color: var(--p1); }
.priority-P2 { border-left-color: var(--p2); }
.priority-P3 { border-left-color: var(--p3); }

.report-card:hover {
  border-color: var(--glass-border-hover);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

/* Priority glow on hover */
.priority-P0:hover { box-shadow: var(--shadow-lg), -2px 0 12px rgba(239, 68, 68, 0.2); }
.priority-P1:hover { box-shadow: var(--shadow-lg), -2px 0 12px rgba(245, 158, 11, 0.2); }
.priority-P2:hover { box-shadow: var(--shadow-lg), -2px 0 12px rgba(59, 130, 246, 0.2); }
.priority-P3:hover { box-shadow: var(--shadow-lg), -2px 0 12px rgba(99, 102, 241, 0.2); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.category-pill {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
  background: var(--glass);
  border: 1px solid var(--glass-border);
  padding: 0.2rem 0.625rem;
  border-radius: var(--radius-full);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 120px;
}

.card-body {
  flex: 1;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 0.375rem;
  line-height: 1.4;
}

.card-description {
  font-size: 0.875rem;
  color: var(--text-secondary);
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin: 0;
}

.card-meta {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding-top: 0.75rem;
  border-top: 1px solid var(--glass-border);
}

.card-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

/* Status badge in header */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-pending { color: var(--pending); }
.status-pending .status-dot { background: var(--pending); box-shadow: 0 0 6px var(--pending); }

.status-resolved { color: var(--success); }
.status-resolved .status-dot { background: var(--success); box-shadow: 0 0 6px var(--success); }

.status-rejected,
.status-refused { color: var(--error); }
.status-rejected .status-dot,
.status-refused .status-dot { background: var(--error); box-shadow: 0 0 6px var(--error); }

.status-escalated { color: var(--primary-light); }
.status-escalated .status-dot { background: var(--primary-light); box-shadow: 0 0 6px var(--primary-light); }

.status-meeting-requested { color: var(--accent); }
.status-meeting-requested .status-dot { background: var(--accent); box-shadow: 0 0 6px var(--accent); }

/* Refusal section */
.refusal-section {
  padding: 0.875rem 1rem;
  background: var(--error-bg);
  border-radius: var(--radius-md);
  border: 1px solid rgba(239, 68, 68, 0.2);
  animation: slide-up 0.25s ease;
}

.refusal-header {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--error);
  margin-bottom: 0.375rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.refusal-text {
  font-size: 0.875rem;
  color: var(--text-main);
  line-height: 1.5;
  margin: 0;
}
</style>