<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bootstrap demo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  @auth
    <meta name="user-id" content="{{ auth()->id() }}">
  @endauth
</head>

<body>
  <div class="container ">
    <a href="/notes" class="btn btn-primary">
      Go to All Notes
    </a>
  </div>
  <div class="container mt-4" id="forms-container">
    <div class="form-card position-relative border rounded p-4 mb-3">
      {{-- <button type="button" class="btn-close position-absolute top-0 end-0 m-3" onclick="closeForm(this)">
      </button> --}}

      <form id="formId">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" name="title">
        </div>
        <div class="mb-3">
          <label class="form-label">Note</label>
          <input type="text" class="form-control" name="note">
        </div>

        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-success rounded-circle shadow"
            style="width:45px;height:45px;font-size:22px;line-height:1;" onclick="addForm(this)">
            +
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Global submit button outside the cards -->
  <div class="container mt-2">
    <button type="button" class="btn btn-primary w-100" onclick="submitAll()">
      Submit All
    </button>
  </div>

  <script>
    function addForm(btn) {
      btn.remove();

      const newCard = document.createElement('div');
      newCard.className = 'form-card position-relative border rounded p-4 mb-3';
      newCard.innerHTML = `
      <button 
        type="button" 
        class="btn-close position-absolute top-0 end-0 m-3"
        onclick="closeForm(this)">
      </button>
      <form>
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" name="title">
        </div>
        <div class="mb-3">
          <label class="form-label">Note</label>
          <input type="text" class="form-control" name="note">
        </div>
        <div class="d-flex justify-content-end">
          <button 
            type="button" 
            class="btn btn-success rounded-circle shadow"
            style="width:45px;height:45px;font-size:22px;line-height:1;"
            onclick="addForm(this)">
            +
          </button>
        </div>
      </form>
    `;

      document.getElementById('forms-container').appendChild(newCard);
    }

    function closeForm(btn) {
      const currentCard = btn.closest('.form-card');
      const previousCard = currentCard.previousElementSibling;

      // Restore + button to previous card if it's missing
      if (previousCard && previousCard.classList.contains('form-card')) {
        const hasPlus = previousCard.querySelector('[onclick="addForm(this)"]');
        if (!hasPlus) {
          const btnDiv = previousCard.querySelector('.d-flex');
          const plusBtn = document.createElement('button');
          plusBtn.type = 'button';
          plusBtn.className = 'btn btn-success rounded-circle shadow';
          plusBtn.style = 'width:45px;height:45px;font-size:22px;line-height:1;';
          plusBtn.setAttribute('onclick', 'addForm(this)');
          plusBtn.textContent = '+';
          btnDiv.appendChild(plusBtn);
        }
      }

      currentCard.remove();
    }

    function submitAll() {
      const forms = document.querySelectorAll('#forms-container form');
      const allData = [];

      forms.forEach((form, index) => {
        const title = form.querySelector('[name="title"]').value;
        const note = form.querySelector('[name="note"]').value;
        allData.push({ index: index + 1, title, note });
      });

      console.log('Submitting all forms:', allData);

      // If using fetch to send to your Laravel backend:
      fetch('{{ route("notes.store") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ forms: allData })
      })
        .then(res => res.json())
        .then(data => {
          alert('New notes added successfully');

          document.querySelector('#formId').reset();
        })
        .catch(err => console.error('Error:', err));
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
    integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y"
    crossorigin="anonymous"></script>

  @vite(['resources/js/app.js'])
</body>

</html>