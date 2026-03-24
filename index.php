<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Swypply — Swipe Your Way to Your Next Job</title>
<meta name="description" content="Swypply uses AI to tailor your CV for every job and auto-applies for you. Swipe right to apply, swipe left to pass.">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --orange: #f97316;
    --orange-dark: #ea6c0a;
    --bg: #0f0f0f;
    --surface: #1a1a1a;
    --border: #2a2a2a;
    --text: #f1f1f1;
    --muted: #888;
  }
  html { scroll-behavior: smooth; }
  body { background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; }

  /* NAV */
  nav { position: sticky; top: 0; z-index: 100; background: rgba(15,15,15,.92);
        backdrop-filter: blur(12px); border-bottom: 1px solid var(--border);
        padding: 16px 32px; display: flex; align-items: center; gap: 32px; }
  .nav-brand { font-size: 20px; font-weight: 800; color: var(--orange); letter-spacing: -0.5px; }
  nav a { color: var(--muted); text-decoration: none; font-size: 14px; }
  nav a:hover { color: var(--text); }
  .nav-cta { margin-left: auto; background: var(--orange); color: #fff !important;
             padding: 9px 20px; border-radius: 8px; font-weight: 600; }
  .nav-cta:hover { background: var(--orange-dark) !important; }

  /* HERO */
  .hero { text-align: center; padding: 100px 24px 80px; max-width: 780px; margin: 0 auto; }
  .hero-tag { display: inline-block; background: rgba(249,115,22,.15); color: var(--orange);
              padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;
              margin-bottom: 28px; border: 1px solid rgba(249,115,22,.3); }
  h1 { font-size: clamp(36px, 6vw, 64px); font-weight: 800; line-height: 1.1;
       letter-spacing: -1.5px; margin-bottom: 20px; }
  h1 span { color: var(--orange); }
  .hero p { font-size: 18px; color: var(--muted); max-width: 520px; margin: 0 auto 40px; }
  .hero-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
  .btn-primary { background: var(--orange); color: #fff; padding: 16px 32px; border-radius: 12px;
                 font-size: 16px; font-weight: 700; text-decoration: none; display: inline-flex;
                 align-items: center; gap: 8px; }
  .btn-primary:hover { background: var(--orange-dark); }
  .btn-ghost { background: var(--surface); color: var(--text); padding: 16px 32px; border-radius: 12px;
               font-size: 16px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); }
  .btn-ghost:hover { background: #222; }

  /* FEATURES */
  .features { padding: 80px 24px; max-width: 1100px; margin: 0 auto; }
  .section-label { text-align: center; color: var(--orange); font-weight: 600; font-size: 13px;
                   letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px; }
  h2 { text-align: center; font-size: clamp(28px, 4vw, 42px); font-weight: 800;
       letter-spacing: -1px; margin-bottom: 56px; }
  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
  .card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 32px; }
  .card-icon { font-size: 32px; margin-bottom: 16px; }
  .card h3 { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
  .card p { color: var(--muted); font-size: 15px; }

  /* HOW IT WORKS */
  .how { padding: 80px 24px; background: var(--surface); }
  .how-inner { max-width: 860px; margin: 0 auto; }
  .steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; margin-top: 56px; }
  .step { text-align: center; }
  .step-num { width: 48px; height: 48px; background: var(--orange); border-radius: 50%;
              font-size: 20px; font-weight: 800; display: flex; align-items: center;
              justify-content: center; margin: 0 auto 16px; }
  .step h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
  .step p { color: var(--muted); font-size: 14px; }

  /* PRICING */
  .pricing { padding: 80px 24px; max-width: 900px; margin: 0 auto; }
  .plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-top: 56px; }
  .plan { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 32px; }
  .plan.featured { border-color: var(--orange); position: relative; }
  .plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
                background: var(--orange); color: #fff; padding: 4px 16px; border-radius: 20px;
                font-size: 12px; font-weight: 700; white-space: nowrap; }
  .plan-name { font-size: 13px; font-weight: 600; color: var(--muted); text-transform: uppercase;
               letter-spacing: 1px; margin-bottom: 8px; }
  .plan-price { font-size: 36px; font-weight: 800; margin-bottom: 4px; }
  .plan-price span { font-size: 16px; color: var(--muted); font-weight: 400; }
  .plan-desc { color: var(--muted); font-size: 14px; margin-bottom: 24px; }
  .plan ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .plan li { font-size: 14px; display: flex; gap: 8px; }
  .plan li::before { content: '✓'; color: var(--orange); font-weight: 700; }

  /* FOOTER */
  footer { border-top: 1px solid var(--border); padding: 40px 24px; text-align: center; color: var(--muted); font-size: 13px; }
  footer .brand { color: var(--orange); font-weight: 700; font-size: 16px; display: block; margin-bottom: 8px; }

  @media (max-width: 600px) {
    nav { padding: 14px 20px; gap: 20px; }
    .hero { padding: 72px 20px 60px; }
  }
</style>
</head>
<body>

<nav>
  <span class="nav-brand">Swypply</span>
  <a href="#features">Features</a>
  <a href="#pricing">Pricing</a>
  <a href="#" class="nav-cta">Download App</a>
</nav>

<section class="hero">
  <div class="hero-tag">🚀 AI-Powered Job Applications</div>
  <h1>Swipe your way to your <span>next job</span></h1>
  <p>Swypply finds jobs, tailors your CV with AI, and applies for you — all with a simple swipe.</p>
  <div class="hero-btns">
    <a href="#" class="btn-primary">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.7 9.05 7.4c1.27.06 2.15.67 2.9.69 1.1-.23 2.16-.9 3.37-.84 1.44.06 2.52.58 3.23 1.5-2.95 1.72-2.46 5.54.5 6.61-.6 1.54-1.37 3.06-2 3.92zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
      App Store
    </a>
    <a href="#" class="btn-primary" style="background:#1a73e8">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3.18 23.76c.29.16.63.17.94.04L13.9 12 3.12.19C2.81.07 2.47.08 2.18.23 1.62.54 1.25 1.14 1.25 1.82v20.36c0 .68.37 1.28.93 1.58zM15.34 13.42l2.06 2.06-9.44 5.45 7.38-7.51zm2.06-4.9L15.34 10.6 7.96 3.09l9.44 5.43zM21.1 10.3l-2.62-1.51-2.31 2.31 2.31 2.31 2.65-1.53c.75-.44.75-1.14-.03-1.58z"/></svg>
      Play Store
    </a>
    <a href="#features" class="btn-ghost">Learn more</a>
  </div>
</section>

<section class="features" id="features">
  <div class="section-label">What you get</div>
  <h2>Everything you need to land the job</h2>
  <div class="grid">
    <div class="card">
      <div class="card-icon">💼</div>
      <h3>Thousands of Jobs</h3>
      <p>Browse jobs from multiple sources — local and international — all in one feed.</p>
    </div>
    <div class="card">
      <div class="card-icon">🤖</div>
      <h3>AI CV Tailoring</h3>
      <p>Claude AI rewrites your CV and cover letter to perfectly match each job description.</p>
    </div>
    <div class="card">
      <div class="card-icon">👆</div>
      <h3>Swipe to Apply</h3>
      <p>Swipe right to apply, left to skip. It's that simple — no more filling out long forms.</p>
    </div>
    <div class="card">
      <div class="card-icon">📄</div>
      <h3>PDF Export</h3>
      <p>Download your tailored CV and cover letter as a professional PDF instantly.</p>
    </div>
    <div class="card">
      <div class="card-icon">📱</div>
      <h3>Works Offline</h3>
      <p>Your CV and application history are saved locally — always accessible.</p>
    </div>
    <div class="card">
      <div class="card-icon">🔔</div>
      <h3>Job Alerts</h3>
      <p>Get notified when new jobs matching your skills are posted.</p>
    </div>
  </div>
</section>

<section class="how">
  <div class="how-inner">
    <div class="section-label" style="text-align:center">How it works</div>
    <h2>Three steps to your next job</h2>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <h3>Build Your CV</h3>
        <p>Enter your experience, education and skills once in the app.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <h3>Swipe Jobs</h3>
        <p>Browse jobs in your field. Swipe right on anything that interests you.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <h3>AI Does the Rest</h3>
        <p>AI tailors your CV and cover letter, then submits your application.</p>
      </div>
    </div>
  </div>
</section>

<section class="pricing" id="pricing">
  <div class="section-label">Pricing</div>
  <h2>Simple, affordable plans</h2>
  <div class="plans">
    <div class="plan">
      <div class="plan-name">Free</div>
      <div class="plan-price">GHS 0 <span>/mo</span></div>
      <div class="plan-desc">Get started at no cost</div>
      <ul>
        <li>3 AI-tailored applications</li>
        <li>CV builder</li>
        <li>Job search & browse</li>
        <li>PDF export</li>
      </ul>
    </div>
    <div class="plan featured">
      <div class="plan-badge">Most Popular</div>
      <div class="plan-name">Basic</div>
      <div class="plan-price">GHS 25 <span>/mo</span></div>
      <div class="plan-desc">For active job seekers</div>
      <ul>
        <li>20 AI-tailored applications</li>
        <li>CV builder</li>
        <li>Job search & browse</li>
        <li>PDF export</li>
        <li>Priority job alerts</li>
      </ul>
    </div>
    <div class="plan">
      <div class="plan-name">Pro</div>
      <div class="plan-price">GHS 60 <span>/mo</span></div>
      <div class="plan-desc">Unlimited everything</div>
      <ul>
        <li>Unlimited AI applications</li>
        <li>CV builder</li>
        <li>Job search & browse</li>
        <li>PDF export</li>
        <li>Priority job alerts</li>
        <li>Early access to new features</li>
      </ul>
    </div>
  </div>
</section>

<footer>
  <span class="brand">Swypply</span>
  &copy; <?= date('Y') ?> Swypply. All rights reserved. &nbsp;·&nbsp;
  <a href="mailto:hello@swypply.com" style="color:var(--muted)">hello@swypply.com</a>
</footer>

</body>
</html>
