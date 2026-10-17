<script>
    // Saved theme ko CSS paint se pehle <html> par lagao (white flash na ho)
    try {
        if (localStorage.getItem('smartschool-theme') === 'dark-theme') {
            document.documentElement.classList.add('dark-theme');
        }
    } catch (e) {}
</script>
