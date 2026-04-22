<template>
  <div class="pagination" v-if="totalPages > 1">
    <button 
      class="page-btn" 
      :disabled="currentPage === 1"
      @click="$emit('page-change', currentPage - 1)"
    >
      <ChevronLeft :size="16" />
    </button>
    
    <button 
      v-for="page in visiblePages" 
      :key="page"
      class="page-btn"
      :class="{ active: page === currentPage, ellipsis: page === '...' }"
      :disabled="page === '...'"
      @click="page !== '...' && $emit('page-change', page)"
    >
      {{ page }}
    </button>
    
    <button 
      class="page-btn" 
      :disabled="currentPage === totalPages"
      @click="$emit('page-change', currentPage + 1)"
    >
      <ChevronRight :size="16" />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true },
});

defineEmits(['page-change']);

const visiblePages = computed(() => {
  const pages = [];
  const total = props.totalPages;
  const current = props.currentPage;
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else if (current <= 3) {
    pages.push(1, 2, 3, 4, '...', total);
  } else if (current >= total - 2) {
    pages.push(1, '...', total - 3, total - 2, total - 1, total);
  } else {
    pages.push(1, '...', current - 1, current, current + 1, '...', total);
  }
  
  return pages;
});
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 2rem;
}

.page-btn {
  min-width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
}

.page-btn:hover:not(:disabled) {
  background: var(--surface-hover);
  color: var(--text-main);
}

.page-btn.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-btn.ellipsis {
  border: none;
  cursor: default;
}
</style>
