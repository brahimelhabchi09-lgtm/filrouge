<template>
  <main class="container">
    <header class="header">
      <h1>Generated Reports</h1>
      <nav class="nav">
        <RouterLink to="/generated-reports">List</RouterLink>
        <RouterLink to="/admin/generated-reports/create">Create (Admin)</RouterLink>
      </nav>
    </header>

    <RouterView />
  </main>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router';
</script>

<style scoped>
.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: Arial, sans-serif;
}

.header {
  margin-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 12px;
}

.nav {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.nav a {
  color: #2563eb;
  text-decoration: none;
}

.nav a.router-link-active {
  font-weight: 700;
}
</style>

