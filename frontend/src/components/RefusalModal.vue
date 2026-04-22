<template>
  <transition name="modal">
    <div v-if="isOpen" :id="id" class="modal-backdrop" @click.self="close">
      <div class="modal">
        <h2 class="modal-title">Refuse Report</h2>
        <p class="modal-description">Please provide a reason for refusing this report. This will be shared with the student.</p>
        
        <div class="form-group">
          <label>Reason for refusal</label>
          <textarea v-model="reason" class="form-control" placeholder="Explain why this report is being refused..."></textarea>
        </div>

        <div class="modal-actions">
          <button class="btn btn-outline" @click="close">Cancel</button>
          <button class="btn btn-danger" @click="submit">Refuse Report</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  id: String,
  isOpen: Boolean
});

const emit = defineEmits(['close', 'submit']);
const reason = ref('');

const close = () => {
  reason.value = '';
  emit('close');
};

const submit = () => {
  emit('submit', reason.value);
  close();
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: var(--surface);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
}

.modal-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: var(--text-main);
  font-weight: 600;
}

.modal-description {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-main);
  font-weight: 500;
}

.form-control {
  width: 100%;
  background: var(--background);
  border: 1px solid var(--glass-border);
  border-radius: 12px;
  padding: 0.75rem;
  color: var(--text-main);
  font-family: inherit;
  resize: vertical;
  min-height: 100px;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  border: 1px solid transparent;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  font-family: inherit;
}

.btn-outline {
  background: transparent;
  border-color: var(--glass-border);
  color: var(--text-muted);
}

.btn-outline:hover {
  background: var(--surface-hover);
  color: var(--text-main);
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
