<script>
(function() {
  function bindToggle(id, inputId) {
    const t = document.getElementById(id);
    const inp = document.getElementById(inputId);
    if (!t || !inp) return;
    function go() { inp.type = (inp.type === 'text') ? 'password' : 'text'; }
    t.addEventListener('click', go);
    t.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }});
  }
  bindToggle('toggleNewPassword', 'password');
  bindToggle('toggleConfirmPassword', 'password_confirmation');
})();
</script>