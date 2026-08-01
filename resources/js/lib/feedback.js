import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3200,
  timerProgressBar: true,
  customClass: {
    popup: 'siperdok-toast',
  },
});

export const toast = (icon, title) => {
  Toast.fire({ icon, title: Array.isArray(title) ? title.join(', ') : title });
};

export const confirmAction = async ({
  title = 'Konfirmasi tindakan',
  text = 'Apakah Anda yakin ingin melanjutkan?',
  icon = 'warning',
  confirmButtonText = 'Ya, lanjutkan',
  cancelButtonText = 'Batal',
  confirmButtonColor = '#007bff',
} = {}) => {
  const result = await Swal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText,
    confirmButtonColor,
    reverseButtons: true,
    focusCancel: true,
  });

  return result.isConfirmed;
};

export const apiErrorMessage = (error, fallback = 'Terjadi kesalahan. Silakan coba lagi.') => {
  const errors = error.response?.data?.errors;

  if (errors && typeof errors === 'object') {
    const firstError = Object.values(errors).flat().find(Boolean);
    if (firstError) {
      return firstError;
    }
  }

  return error.response?.data?.message || fallback;
};

export const apiErrorMessages = (error, fallback = 'Terjadi kesalahan. Silakan coba lagi.') => {
  const errors = error.response?.data?.errors;

  if (errors && typeof errors === 'object') {
    return Object.values(errors).flat().filter(Boolean);
  }

  return [error.response?.data?.message || fallback];
};
