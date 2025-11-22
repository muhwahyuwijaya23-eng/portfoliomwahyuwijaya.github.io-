document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.section-content');

    // Fungsi untuk menampilkan halaman yang dipilih
    function showSection(id) {
        sections.forEach(section => {
            if (section.id === id) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        });
    }
    document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.section-content').forEach(sec => sec.classList.add('hidden'))
        document.querySelector(this.getAttribute('href')).classList.remove('hidden');
    });
});


    // Fungsi untuk mengaktifkan link navigasi
    function setActiveLink(clickedLink) {
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        clickedLink.classList.add('active');
    }

    // Event listener untuk setiap link navigasi
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault(); // Mencegah perilaku default anchor link
            const targetId = link.getAttribute('href').substring(1); // Ambil ID target (misal: 'about', 'resume')
            
            showSection(targetId);
            setActiveLink(link);
            
            // Opsional: memperbarui URL tanpa me-reload halaman
            history.pushState({ section: targetId }, '', `#${targetId}`);
        });
    });

    // Menangani navigasi mundur/maju browser
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.section) {
            showSection(e.state.section);
            const activeLink = document.querySelector(`.nav-link[href="#${e.state.section}"]`);
            if (activeLink) {
                setActiveLink(activeLink);
            }
        }
    });

    // Tampilkan halaman About secara default saat pertama kali dimuat
    if (window.location.hash) {
        const initialSection = window.location.hash.substring(1);
        const initialLink = document.querySelector(`.nav-link[href="${window.location.hash}"]`);
        showSection(initialSection);
        if (initialLink) {
            setActiveLink(initialLink);
        }
    } else {
        showSection('about');
        document.querySelector('.nav-link[href="#about"]').classList.add('active');
    }
});