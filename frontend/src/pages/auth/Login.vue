<template>
  <div class="login-wrapper">
    <!-- Login form side -->
    <div class="login-container">
      <div class="login-brand">
        <div class="brand-icon">
          <ShieldCheck :size="32" />
        </div>
        <h1 class="brand-title">YOUPORTS</h1>
        <p class="brand-subtitle">Campus Issue Reporting System</p>
      </div>

      <div class="login-card">
        <div class="login-header">
          <h2>Welcome back</h2>
          <p>Sign in to your account to continue</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label>Email address</label>
            <div class="input-wrapper">
              <Mail :size="16" class="input-icon" />
              <input
                v-model="form.email"
                type="email"
                class="form-control with-icon"
                placeholder="your.email@school.com"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
              <Lock :size="16" class="input-icon" />
              <input
                v-model="form.password"
                type="password"
                class="form-control with-icon"
                placeholder="Enter your password"
                required
              />
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input v-model="form.remember" type="checkbox" />
              <span class="checkmark"></span>
              Remember me
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-signin" :disabled="loading">
            <Loader2 v-if="loading" :size="18" class="spin" />
            <span v-else>Sign In</span>
          </button>

          <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
        </form>
      </div>

      <p class="login-footer">© 2025 YouPorts — Campus Issue Management</p>
    </div>

    <!-- Visual panel -->
    <div class="login-visual">
      <div class="visual-orb orb-a"></div>
      <div class="visual-orb orb-b"></div>
      <div class="visual-orb orb-c"></div>
      <div class="visual-content">
        <h2 class="visual-headline">Report.<br>Prioritize.<br>Resolve.</h2>
        <p class="visual-sub">A unified platform for campus issue management — from reporting to resolution, all in one place.</p>
        <div class="feature-grid">
          <div class="feature-chip">
            <CheckCircle2 :size="16" />
            <span>Real-time Tracking</span>
          </div>
          <div class="feature-chip">
            <CheckCircle2 :size="16" />
            <span>Smart Prioritization</span>
          </div>
          <div class="feature-chip">
            <CheckCircle2 :size="16" />
            <span>Team Collaboration</span>
          </div>
          <div class="feature-chip">
            <CheckCircle2 :size="16" />
            <span>Automated Reports</span>
          </div>
        </div>
        <!-- Floating mock report card -->
        <div class="mock-card">
          <div class="mock-card-header">
            <span class="mock-pill">Infrastructure</span>
            <div class="mock-status">
              <span class="mock-dot pending"></span>
              <span>Pending</span>
            </div>
          </div>
          <p class="mock-title">Broken heating in Block B, Room 204</p>
          <div class="mock-footer">
            <span class="mock-badge p1">⬤ P1</span>
            <span class="mock-date">Apr 20, 2025</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { ShieldCheck, Mail, Lock, Loader2, CheckCircle2 } from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const errorMessage = ref('');
const loading = ref(false);

const handleLogin = async () => {
  errorMessage.value = '';
  loading.value = true;
  try {
    await authStore.login(form.email, form.password);
    const roleRoutes = {
      'ADMIN': '/admin/dashboard',
      'BDE': '/bde/dashboard',
      'TEACHER': '/teacher/dashboard',
      'STUDENT': '/student/dashboard'
    };
    const role = authStore.userRole || 'STUDENT';
    router.push(roleRoutes[role] || '/student/dashboard');
  } catch (error) {
    errorMessage.value = error.message || 'Invalid credentials';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-wrapper {
  display: grid;
  grid-template-columns: 440px 1fr;
  min-height: 100vh;
}

/* ── Form column ─────────────────────────────── */
.login-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2.5rem 2rem;
  gap: 1.75rem;
  background: var(--surface);
  border-right: 1px solid var(--glass-border);
  position: relative;
  z-index: 1;
}

.login-brand { text-align: center; }

.brand-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: white;
  box-shadow: 0 8px 32px var(--primary-glow);
  animation: pulse-glow 3s ease-in-out infinite;
}

.brand-title {
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 0.25rem;
}

.brand-subtitle {
  color: var(--text-muted);
  font-size: 0.8125rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.login-card {
  width: 100%;
  max-width: 360px;
  background: var(--surface-card);
  border: 1px solid var(--glass-border);
  border-top: 3px solid;
  border-image: linear-gradient(90deg, var(--primary), var(--accent)) 1;
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  box-shadow: var(--shadow-card);
}

.login-header {
  margin-bottom: 1.5rem;
}

.login-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.25rem;
  color: var(--text-main);
}

.login-header p {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin: 0;
}

.login-form { display: flex; flex-direction: column; gap: 1.25rem; }

.form-group { display: flex; flex-direction: column; gap: 0.4rem; }

.form-group label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.input-wrapper { position: relative; }

.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  pointer-events: none;
}

.form-control {
  width: 100%;
  background: var(--background);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  padding: 0.7rem 0.875rem;
  color: var(--text-main);
  font-family: inherit;
  font-size: 0.9rem;
  transition: all var(--transition-fast);
}

.form-control.with-icon { padding-left: 2.5rem; }

.form-control::placeholder { color: var(--text-muted); }

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: var(--ring-glow);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: var(--text-muted);
  font-size: 0.8125rem;
}

.checkbox-label input {
  width: 15px;
  height: 15px;
  accent-color: var(--primary);
}

.forgot-link {
  color: var(--primary-light);
  font-size: 0.8125rem;
  transition: color var(--transition-fast);
}

.forgot-link:hover { color: var(--primary); }

.btn-signin {
  width: 100%;
  padding: 0.8rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.9375rem;
  font-family: inherit;
  cursor: pointer;
  border: none;
  background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
  background-size: 200% 200%;
  color: white;
  box-shadow: 0 4px 15px var(--primary-glow);
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-signin:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px var(--primary-glow);
  animation: gradient-shift 2s ease infinite;
}

.btn-signin:disabled { opacity: 0.6; cursor: not-allowed; }

.spin { animation: spin 0.8s linear infinite; }

.error-text {
  color: var(--error);
  font-size: 0.8125rem;
  text-align: center;
  background: var(--error-bg);
  padding: 0.625rem;
  border-radius: var(--radius-md);
  border: 1px solid rgba(239, 68, 68, 0.2);
  margin: 0;
}

.login-footer {
  color: var(--text-muted);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
}

/* ── Visual column ───────────────────────────── */
.login-visual {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--background);
  overflow: hidden;
  padding: 3rem 2rem;
}

.visual-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  pointer-events: none;
}

.orb-a {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, transparent 70%);
  top: -100px;
  right: 0;
  animation: float 18s ease-in-out infinite;
}

.orb-b {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, transparent 70%);
  bottom: -80px;
  left: 10%;
  animation: float 22s ease-in-out infinite reverse;
}

.orb-c {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.25) 0%, transparent 70%);
  top: 40%;
  left: 40%;
  animation: float 15s ease-in-out infinite;
  animation-delay: -5s;
}

.visual-content {
  position: relative;
  z-index: 1;
  max-width: 440px;
}

.visual-headline {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -0.025em;
  margin: 0 0 1.25rem;
  background: linear-gradient(135deg, var(--text-main) 0%, var(--primary-light) 60%, var(--secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.visual-sub {
  font-size: 1rem;
  color: var(--text-muted);
  line-height: 1.7;
  margin: 0 0 2rem;
}

.feature-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.625rem;
  margin-bottom: 2rem;
}

.feature-chip {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 0.875rem;
  background: var(--glass);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.feature-chip svg { color: var(--success); flex-shrink: 0; }

/* ── Mock report card ────────────────────────── */
.mock-card {
  background: var(--surface-elevated);
  border: 1px solid var(--glass-border);
  border-left: 4px solid var(--p1);
  border-radius: var(--radius-lg);
  padding: 1rem 1rem 1rem 0.875rem;
  box-shadow: var(--shadow-card), 0 0 30px rgba(99, 102, 241, 0.12);
  animation: float 5s ease-in-out infinite;
}

.mock-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.625rem;
}

.mock-pill {
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  background: var(--glass);
  border: 1px solid var(--glass-border);
  padding: 0.15rem 0.5rem;
  border-radius: var(--radius-full);
}

.mock-status {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--pending);
}

.mock-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
}

.mock-dot.pending { background: var(--pending); box-shadow: 0 0 4px var(--pending); }

.mock-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-main);
  margin: 0 0 0.75rem;
  line-height: 1.4;
}

.mock-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--glass-border);
  padding-top: 0.625rem;
}

.mock-badge {
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.15rem 0.5rem;
  border-radius: var(--radius-full);
}

.mock-badge.p1 { background: var(--p1-bg); color: var(--p1); border: 1px solid rgba(245, 158, 11, 0.2); }

.mock-date { font-size: 0.6rem; color: var(--text-muted); }

/* ── Responsive ──────────────────────────────── */
@media (max-width: 1024px) {
  .login-wrapper { grid-template-columns: 1fr; }
  .login-visual { display: none; }
}

@media (max-width: 480px) {
  .login-container { padding: 1.5rem 1rem; }
  .login-card { padding: 1.25rem; }
  .brand-title { font-size: 1.5rem; }
  .brand-icon { width: 52px; height: 52px; }
  .form-options { flex-direction: column; align-items: flex-start; gap: 0.625rem; }
}
</style>