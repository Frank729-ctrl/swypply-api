<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #0f0f0f; color: #f1f1f1; font-family: system-ui, sans-serif; }
nav { background: #1a1a1a; border-bottom: 1px solid #2a2a2a; padding: 14px 32px;
      display: flex; align-items: center; gap: 32px; }
nav .brand { font-size: 18px; font-weight: 700; color: #f97316; }
nav a { color: #aaa; text-decoration: none; font-size: 14px; }
nav a:hover, nav a.active { color: #fff; }
nav .logout { margin-left: auto; }
main { max-width: 1100px; margin: 0 auto; padding: 36px 24px; }
h1 { font-size: 22px; font-weight: 700; margin-bottom: 24px; }
h2 { font-size: 16px; font-weight: 600; margin: 32px 0 14px; color: #ccc; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; margin-bottom: 36px; }
.stat { background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 10px; padding: 20px 16px; }
.stat-val { font-size: 28px; font-weight: 700; }
.stat-val.orange { color: #f97316; }
.stat-val.green  { color: #4ade80; }
.stat-val.red    { color: #f87171; }
.stat-lbl { font-size: 12px; color: #666; margin-top: 4px; }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { text-align: left; padding: 10px 14px; background: #1a1a1a; color: #888;
     font-weight: 500; border-bottom: 1px solid #2a2a2a; white-space: nowrap; }
td { padding: 12px 14px; border-bottom: 1px solid #1e1e1e; }
tr:hover td { background: #161616; }
.badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 12px;
         background: #2a2a2a; color: #888; }
.badge.paid { background: #f97316; color: #fff; }
.btn { display: inline-block; padding: 10px 18px; background: #f97316; color: #fff;
       border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
       cursor: pointer; text-decoration: none; margin-top: 16px; }
.btn:hover { background: #ea6c0a; }
input[type=text], input[type=password] {
  padding: 10px 14px; background: #111; border: 1px solid #333;
  border-radius: 8px; color: #fff; font-size: 14px; outline: none; }
input:focus { border-color: #f97316; }
</style>
