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

    if (counters.length === 0) {
        return;
    }

    animateCounters();

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
    const mapElement = document.getElementById('map');
    if (!mapElement || !window.google || !google.maps) {
        return;
    }

    const myLatLng = { lat: -12.154539363970722, lng: -76.97475567951984 };
    const map = new google.maps.Map(mapElement, {
        zoom: 12,
        center: myLatLng,
    });

    new google.maps.Marker({
        position: myLatLng,
        map,
        title: 'Nuestra Oficina',
    });
}
