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
  background: #080808;
  border: 1px solid rgba(255,255,255,0.05);
  border-left: 3px solid rgba(255,255,255,0.08);
  border-radius: var(--radius-lg);
  padding: 1.125rem;
  transition: all var(--transition-normal);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.6);
  animation: slide-up 0.4s cubic-bezier(0.4,0,0.2,1) both;
  position: relative;
  overflow: hidden;
}

.report-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.015) 0%, transparent 50%);
  pointer-events: none;
}

.priority-P0 { border-left-color: var(--p0); }
.priority-P1 { border-left-color: var(--p1); }
.priority-P2 { border-left-color: var(--p2); }
.priority-P3 { border-left-color: var(--p3); }

.report-card:hover {
  border-color: rgba(124,58,237,0.25);
  transform: translateY(-3px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.8), 0 0 0 1px rgba(124,58,237,0.1);
}

.priority-P0:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.8), -2px 0 16px rgba(244,63,94,0.25); }
.priority-P1:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.8), -2px 0 16px rgba(245,158,11,0.25); }
.priority-P2:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.8), -2px 0 16px rgba(6,182,212,0.25); }
.priority-P3:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.8), -2px 0 16px rgba(124,58,237,0.25); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  flex-shrink: 0;
}

.category-pill {
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  padding: 0.175rem 0.5rem;
  border-radius: var(--radius-full);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 110px;
}

.card-body { flex: 1; }

.card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 0.3rem;
  line-height: 1.4;
  letter-spacing: -0.01em;
}

.card-description {
  font-size: 0.8125rem;
  color: rgba(255,255,255,0.35);
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
  gap: 0.375rem;
  font-size: 0.75rem;
  color: rgba(255,255,255,0.25);
}

.card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding-top: 0.625rem;
  border-top: 1px solid rgba(255,255,255,0.04);
}

.card-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
}

.status-dot {
  width: 5px; height: 5px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-pending { color: var(--pending); }
.status-pending .status-dot { background: var(--pending); box-shadow: 0 0 6px var(--pending); animation: neon-pulse 1.5s ease-in-out infinite; }

.status-resolved { color: var(--success); }
.status-resolved .status-dot { background: var(--success); box-shadow: 0 0 6px var(--success); }

.status-rejected, .status-refused { color: var(--error); }
.status-rejected .status-dot, .status-refused .status-dot { background: var(--error); box-shadow: 0 0 6px var(--error); }

.status-escalated { color: var(--primary-light); }
.status-escalated .status-dot { background: var(--primary-light); box-shadow: 0 0 6px var(--primary-light); animation: neon-pulse 2s ease-in-out infinite; }

.status-meeting-requested { color: var(--accent); }
.status-meeting-requested .status-dot { background: var(--accent); box-shadow: 0 0 6px var(--accent); }

/* Refusal */
.refusal-section {
  padding: 0.75rem;
  background: rgba(244,63,94,0.06);
  border-radius: var(--radius-md);
  border: 1px solid rgba(244,63,94,0.15);
  animation: slide-up 0.25s ease;
}

.refusal-header {
  display: flex; align-items: center; gap: 0.375rem;
  font-size: 0.625rem; font-weight: 700; color: var(--error);
  margin-bottom: 0.375rem; text-transform: uppercase; letter-spacing: 0.06em;
}

.refusal-text { font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.5; margin: 0; }
</style>