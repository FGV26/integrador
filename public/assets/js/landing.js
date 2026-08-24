document.addEventListener("DOMContentLoaded", function () {
  const nav = document.querySelector("[data-nav]");
  const menuToggle = document.querySelector("[data-menu-toggle]");
  const menu = document.querySelector("[data-menu]");
  const profile = document.querySelector("[data-profile]");
  const profileToggle = document.querySelector("[data-profile-toggle]");

  function syncScrolledState() {
    if (!nav) return;
    nav.classList.toggle("is-scrolled", window.scrollY > 24);
  }

  syncScrolledState();
  window.addEventListener("scroll", syncScrolledState);

  if (menuToggle && menu) {
    menuToggle.addEventListener("click", function () {
      menu.classList.toggle("is-open");
    });

    menu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        menu.classList.remove("is-open");
      });
    });
  }

  if (profile && profileToggle) {
    profileToggle.addEventListener("click", function (event) {
      event.stopPropagation();
      profile.classList.toggle("is-open");
    });

    document.addEventListener("click", function (event) {
      if (!profile.contains(event.target)) {
        profile.classList.remove("is-open");
      }
    });
  }

  const counters = document.querySelectorAll("[data-count]");
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;

      const element = entry.target;
      const target = Number(element.getAttribute("data-count") || "0");
      const suffix = element.getAttribute("data-suffix") || "";
      const duration = 1800;
      const start = performance.now();

      function animate(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = Math.floor(target * eased).toLocaleString() + suffix;

        if (progress < 1) {
          requestAnimationFrame(animate);
        }
      }

      requestAnimationFrame(animate);
      observer.unobserve(element);
    });
  }, { threshold: 0.45 });

  counters.forEach(function (counter) {
    observer.observe(counter);
  });
});
