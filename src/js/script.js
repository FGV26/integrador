document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.count');

    const resetCounters = () => {
        counters.forEach(counter => {
            counter.innerText = '0';
        });
    };

    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCounter = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / 200;

                if (count < target) {
                    counter.innerText = `${Math.ceil(count + increment)}`;
                    setTimeout(updateCounter, 10);
                } else {
                    counter.innerText = target;
                }
            };
            updateCounter();
        });
    };

    // Inicializar la animación de los contadores
    animateCounters();

    // Reiniciar y reactivar la animación en cada scroll usando IntersectionObserver
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                resetCounters();
                animateCounters();
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        observer.observe(counter);
    });
});

function initMap() {
    const myLatLng = { lat: -12.0464, lng: -77.0428 }; // Coordenadas de Lima, Perú
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: myLatLng,
    });
    new google.maps.Marker({
        position: myLatLng,
        map,
        title: "Nuestra Oficina",
    });
}

