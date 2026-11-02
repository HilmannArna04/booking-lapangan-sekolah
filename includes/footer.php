<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.js-delete-jadwal-form').forEach(form => {
    form.addEventListener('submit', event => {
        event.preventDefault();

        Swal.fire({
            title: 'Hapus jadwal?',
            text: 'Jadwal yang masih memiliki booking tidak dapat dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#155EEF',
            cancelButtonColor: '#667085'
        }).then(result => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
  document.addEventListener('DOMContentLoaded', function() {
    const flash = document.getElementById('app-flash-message');
    if (!flash || !window.Swal) return;
    const type = flash.dataset.type || 'info';
    const titles = {
      success: 'Berhasil',
      error: 'Terjadi Kesalahan',
      warning: 'Perhatian',
      info: 'Informasi',
      question: 'Konfirmasi'
    };
    Swal.fire({
      icon: type,
      title: titles[type] || 'Informasi',
      text: flash.dataset.message || '',
      confirmButtonText: 'Mengerti',
      confirmButtonColor: '#155EEF',
      background: '#FFFFFF',
      color: '#102A56',
      customClass: {
        popup: 'booking-swal-popup',
        title: 'booking-swal-title',
        confirmButton: 'booking-swal-confirm'
      }
    });
  });
</script>
<script src="/booking-lapangan-sekolah/assets/js/script.js"></script>
</body>

</html>