// reservar.js
document.addEventListener('DOMContentLoaded', function () {
  const params = new URLSearchParams(window.location.search);
  const exp = params.get('exp') || 'Mi experiencia';
  document.getElementById('tituloExp').textContent = decodeURIComponent(exp);

  const inputFecha = document.getElementById('fecha');
  const personas = document.getElementById('personas');
  const btnPagar = document.getElementById('btnPagar');
  const status = document.getElementById('status');

  // Inicializar flatpickr
  flatpickr(inputFecha, {
    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
    minDate: "today",
    onChange: function(selectedDates, dateStr) {
      console.log('Fecha elegida:', dateStr);
    }
  });

  btnPagar.addEventListener('click', async function () {
    const fecha = inputFecha.value; // devuelve formato Y-m-d gracias a dateFormat
    const cant = parseInt(personas.value || 1, 10);

    if (!fecha) {
      alert('Por favor seleccioná una fecha.');
      return;
    }

    btnPagar.disabled = true;
    status.textContent = 'Creando sesión de pago...';

    try {
      // Llamamos al backend para crear la sesión de Stripe
      const res = await fetch("https://sbosb.kidcoding.site/create-checkout.php", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          fecha: fecha,
          experiencia: exp,
          personas: cant
        })
      });

      const data = await res.json();

      if (data.url) {
        // Redirigimos a Stripe Checkout (hosteado por Stripe)
        window.location.href = data.url;
      } else {
        throw new Error(JSON.stringify(data));
      }
    } catch (err) {
      console.error(err);
      alert('Error creando la sesión de pago. Mirá la consola para más detalles.');
      btnPagar.disabled = false;
      status.textContent = '';
    }
  });
});
