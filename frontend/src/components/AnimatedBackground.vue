<template>
  <div class="bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="orb orb-4"></div>
    <div class="grid"></div>
    <div class="scan"></div>
    <div class="vignette"></div>
  </div>
</template>

<style scoped>
.bg {
  position: fixed;
  inset: 0;
  z-index: -1;
  background: #000;
  overflow: hidden;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  animation: drift 25s ease-in-out infinite;
}

.orb-1 {
  width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(124,58,237,0.18) 0%, transparent 70%);
  top: -200px; right: -150px;
  animation-duration: 28s;
}

.orb-2 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(6,182,212,0.12) 0%, transparent 70%);
  bottom: -200px; left: -150px;
  animation-duration: 32s;
  animation-delay: -10s;
}

.orb-3 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(236,72,153,0.1) 0%, transparent 70%);
  top: 40%; left: 30%;
  animation-duration: 22s;
  animation-delay: -5s;
}

.orb-4 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%);
  top: 20%; left: 60%;
  animation-duration: 35s;
  animation-delay: -18s;
}

.grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(124,58,237,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(124,58,237,0.04) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 100%);
}

.scan {
  position: absolute;
  left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, rgba(124,58,237,0.4) 30%, rgba(6,182,212,0.6) 50%, rgba(124,58,237,0.4) 70%, transparent 100%);
  animation: scan 8s linear infinite;
  box-shadow: 0 0 20px rgba(124,58,237,0.3);
}

.vignette {
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at center, transparent 40%, rgba(0,0,0,0.7) 100%);
  pointer-events: none;
}

@keyframes drift {
  0%, 100% { transform: translate(0, 0) scale(1); }
  25%  { transform: translate(30px, -40px) scale(1.06); }
  50%  { transform: translate(-20px, 30px) scale(0.94); }
  75%  { transform: translate(-35px, -20px) scale(1.03); }
}

@keyframes scan {
  0%   { top: -2px; opacity: 0; }
  5%   { opacity: 1; }
  95%  { opacity: 1; }
  100% { top: 100%; opacity: 0; }
}
</style>
