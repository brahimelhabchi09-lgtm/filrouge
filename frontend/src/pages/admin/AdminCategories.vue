<template>
    <div class="page-container">
      <!-- Header Area -->
      <div class="page-header-actions">
        <div class="header-left">
          <router-link :to="{ name: 'admin-dashboard' }" class="back-link">
            <ArrowLeft :size="16" />
            <span>Back to Control Panel</span>
          </router-link>
          <h1>Categories</h1>
          <p>Manage report categories for the ticketing system.</p>
        </div>
        <button class="btn btn-primary" @click="openModal()">
          <Plus :size="15" />
          <span>Add Category</span>
        </button>
      </div>

      <!-- Content Area -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading categories...</p>
      </div>

      <div v-else-if="categories.length === 0" class="empty-state">
        <Tag :size="48" style="opacity: 0.5; color: var(--primary)" />
        <p>No categories found. Create your first category!</p>
      </div>

      <div v-else class="categories-grid animate-in">
        <div 
          v-for="category in categories" 
          :key="category.id" 
          class="category-card glass-card"
        >
          <div class="category-header">
            <div class="category-icon" :style="{ background: getRandomColor(category.id).bg, color: getRandomColor(category.id).text }">
              <Tag :size="20" />
            </div>
            <div class="category-actions">
              <button class="btn-icon" @click="openModal(category)" title="Edit">
                <Pencil :size="14" />
              </button>
              <button class="btn-icon danger" @click="confirmDelete(category)" title="Delete">
                <Trash2 :size="14" />
              </button>
            </div>
          </div>
          <div class="category-body">
            <h3 class="category-name">{{ category.name }}</h3>
            <p class="category-description">{{ category.description || 'No description provided.' }}</p>
          </div>
          <div class="category-footer">
            <span class="category-id">ID: {{ category.id }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit / Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content glass-card">
        <h3>{{ editingCategory ? 'Edit Category' : 'Add New Category' }}</h3>
        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Category Name</label>
            <input 
              v-model="form.name"
              type="text" 
              class="form-control" 
              placeholder="e.g. Infrastructure, Pedagogy"
              required
            />
            <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
          </div>
          <div class="form-group">
            <label>Description <span class="label-note">(optional)</span></label>
            <textarea 
              v-model="form.description"
              class="form-control textarea" 
              placeholder="Brief description of this category..."
            ></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner-sm"></span>
              <template v-else>{{ editingCategory ? 'Save Changes' : 'Create Category' }}</template>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content glass-card delete-modal">
        <div class="delete-icon">
          <AlertTriangle :size="32" />
        </div>
        <h3>Delete Category</h3>
        <p>Are you sure you want to delete <strong>"{{ categoryToDelete?.name }}"</strong>?</p>
        <p class="warning-text">This action cannot be undone.</p>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="showDeleteModal = false">Cancel</button>
          <button type="button" class="btn btn-danger" @click="deleteCategory" :disabled="deleting">
            <span v-if="deleting" class="spinner-sm"></span>
            <template v-else>Delete Category</template>
          </button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { ArrowLeft, Plus, Tag, Pencil, Trash2, AlertTriangle } from 'lucide-vue-next';

const adminStore = useAdminStore();

const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingCategory = ref(null);
const categoryToDelete = ref(null);
const saving = ref(false);
const deleting = ref(false);

const form = reactive({
  name: '',
  description: ''
});

const errors = reactive({
  name: ''
});

// Using established project colors with transparent backgrounds
const colorPalette = [
  { bg: 'rgba(99, 102, 241, 0.15)', text: '#6366f1' }, // Indigo
  { bg: 'rgba(139, 92, 246, 0.15)', text: '#8b5cf6' }, // Violet
  { bg: 'rgba(236, 72, 153, 0.15)', text: '#ec4899' }, // Pink
  { bg: 'rgba(16, 185, 129, 0.15)', text: '#10b981' }, // Emerald
  { bg: 'rgba(245, 158, 11, 0.15)', text: '#f59e0b' }, // Amber
  { bg: 'rgba(14, 165, 233, 0.15)', text: '#0ea5e9' }  // Sky
];

const getRandomColor = (id) => {
  return colorPalette[(id - 1) % colorPalette.length];
};

onMounted(async () => {
  await fetchCategories();
});

const fetchCategories = async () => {
  loading.value = true;
  try {
    const data = await adminStore.fetchCategories();
    categories.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Error fetching categories:', error);
    categories.value = [];
  } finally {
    loading.value = false;
  }
};

const openModal = (category = null) => {
  editingCategory.value = category;
  errors.name = '';
  
  if (category) {
    form.name = category.name;
    form.description = category.description || '';
  } else {
    form.name = '';
    form.description = '';
  }
  
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingCategory.value = null;
  form.name = '';
  form.description = '';
};

const saveCategory = async () => {
  errors.name = '';
  if (!form.name.trim()) {
    errors.name = 'Category name is required';
    return;
  }

  saving.value = true;
  
  try {
    if (editingCategory.value) {
      await adminStore.updateCategory(editingCategory.value.id, {
        name: form.name,
        description: form.description
      });
    } else {
      await adminStore.createCategory({
        name: form.name,
        description: form.description
      });
    }
    await fetchCategories();
    closeModal();
  } catch (error) {
    errors.name = error.response?.data?.message || error.message || 'Failed to save category';
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (category) => {
  categoryToDelete.value = category;
  showDeleteModal.value = true;
};

const deleteCategory = async () => {
  if (!categoryToDelete.value) return;
  deleting.value = true;
  try {
    await adminStore.deleteCategory(categoryToDelete.value.id);
    await fetchCategories();
    showDeleteModal.value = false;
    categoryToDelete.value = null;
  } catch (error) {
    console.error('Error deleting category:', error);
  } finally {
    deleting.value = false;
  }
};
</script>

<style scoped>
.page-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header-actions {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2.5rem;
  animation: slide-up 0.3s ease both;
}

.header-left {
  display: flex;
  flex-direction: column;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.8125rem;
  font-weight: 500;
  margin-bottom: 1rem;
  transition: color var(--transition-fast);
}

.back-link:hover {
  color: var(--text-main);
}

.header-left h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 0.25rem 0;
}

.header-left p {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin: 0;
}

/* Base button styles matching DashboardLayout */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.25rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.875rem;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all var(--transition-fast);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  color: white;
  box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px var(--primary-glow);
}

.btn-danger { background: var(--error); color: white; }
.btn-danger:hover:not(:disabled) { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4); }

.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid transparent;}
.btn-ghost:hover { background: var(--surface-hover); color: var(--text-main); }


/* Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.category-card {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg), 0 0 0 1px rgba(99, 102, 241, 0.1);
  border-color: var(--glass-border-hover);
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}

.category-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.category-actions {
  display: flex;
  gap: 0.4rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-icon:hover {
  background: var(--surface-hover);
  color: var(--text-main);
  border-color: var(--glass-border-hover);
}

.btn-icon.danger:hover {
  background: rgba(239, 68, 68, 0.1);
  color: var(--error);
  border-color: rgba(239, 68, 68, 0.3);
}

.category-body {
  flex: 1;
}

.category-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-main);
  margin: 0 0 0.5rem 0;
}

.category-description {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.category-footer {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid var(--glass-border);
}

.category-id {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

/* Modals */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; z-index: 200;
  animation: fade-in 0.2s ease;
}

.modal-content {
  max-width: 460px; width: calc(100% - 2rem);
  padding: 2.25rem; animation: slide-up 0.25s ease;
}

.modal-content h3 { font-size: 1.25rem; font-weight: 700; margin: 0 0 1.5rem 0; }

.form-group { margin-bottom: 1.25rem; }

.form-group label {
  display: block; margin-bottom: 0.5rem;
  font-weight: 600; font-size: 0.8125rem; color: var(--text-secondary);
}

.label-note { font-weight: 400; color: var(--text-muted); font-size: 0.75rem; }

.form-control {
  width: 100%; background: var(--background);
  border: 1px solid var(--glass-border); border-radius: var(--radius-md);
  padding: 0.75rem 1rem; color: var(--text-main); font-family: inherit; font-size: 0.875rem;
}

.form-control:focus { outline: none; border-color: var(--primary); box-shadow: var(--ring-glow); }

.textarea { resize: vertical; min-height: 100px; }

.error-message { color: var(--error); font-size: 0.75rem; display: block; margin-top: 0.375rem; }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; }

/* Delete Modal */
.delete-modal { text-align: center; }

.delete-icon {
  width: 56px; height: 56px; background: rgba(239, 68, 68, 0.1); border-radius: 50%;
  display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; color: var(--error);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.delete-modal h3 { margin-bottom: 0.75rem; }
.delete-modal p { color: var(--text-muted); margin: 0 0 0.5rem; font-size: 0.9375rem; }
.warning-text { font-size: 0.8125rem; color: #fca5a5 !important; font-weight: 500; }

@media (max-width: 640px) {
  .page-header-actions { flex-direction: column; align-items: stretch; gap: 1.5rem; }
  .categories-grid { grid-template-columns: 1fr; }
}
</style>
