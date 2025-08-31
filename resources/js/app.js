import Swal from 'sweetalert2';

export function showAlert(title, text) {
  Swal.fire({
    title: title,
    text: text,
    icon: 'warning',
    confirmButtonText: 'OK'
  });
}

export function showLoading() {
  Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    title: 'Memproses ringkasan...',
    didOpen: () => {
      Swal.showLoading();
    }
  });
}

export function closeLoading() {
  Swal.close();
}

document.addEventListener('livewire:load', function () {
  if (window.Livewire) {
    window.Livewire.on('swal:loading', () => {
      showLoading();
    });
    window.Livewire.on('swal:close', () => {
      closeLoading();
    });
  }
});
