<template>
  <span class="priority-badge" :class="`badge-${priority?.toUpperCase()}`">
    <span class="priority-dot"></span>
    {{ priority?.toUpperCase() }}
  </span>
</template>

<script setup>
defineProps({
  priority: {
    type: String,
    default: 'P2'
  }
});
</script>

<style scoped>
.priority-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.2rem 0.625rem;
  border-radius: var(--radius-full);
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border: 1px solid transparent;
}

.priority-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.badge-P0 {
  background: var(--p0-bg);
  color: var(--p0);
  border-color: rgba(239, 68, 68, 0.2);
}
.badge-P0 .priority-dot { background: var(--p0); box-shadow: 0 0 6px var(--p0); }

.badge-P1 {
  background: var(--p1-bg);
  color: var(--p1);
  border-color: rgba(245, 158, 11, 0.2);
}
.badge-P1 .priority-dot { background: var(--p1); box-shadow: 0 0 6px var(--p1); }

.badge-P2 {
  background: var(--p2-bg);
  color: var(--p2);
  border-color: rgba(59, 130, 246, 0.2);
}
.badge-P2 .priority-dot { background: var(--p2); box-shadow: 0 0 6px var(--p2); }

.badge-P3 {
  background: var(--p3-bg);
  color: var(--p3);
  border-color: rgba(99, 102, 241, 0.2);
}
.badge-P3 .priority-dot { background: var(--p3); box-shadow: 0 0 6px var(--p3); }
</style>
