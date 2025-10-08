// assets/js/navbar.js
document.addEventListener('DOMContentLoaded', () => {
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      // Toggle hidden class to show/hide mobile menu
      mobileMenu.classList.toggle('hidden');
      // Optional: Add transition animation
      mobileMenu.classList.toggle('opacity-0');
      mobileMenu.classList.toggle('opacity-100');
    });
  }
});
