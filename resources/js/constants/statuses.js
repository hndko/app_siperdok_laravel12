export const PROJECT_STATUSES = {
  draft: {
    label: 'Draft',
    className: 'badge-draft',
    icon: 'fas fa-pen',
  },
  submitted: {
    label: 'Telah Dikirim',
    className: 'badge-submitted',
    icon: 'fas fa-paper-plane',
  },
  in_review: {
    label: 'Dalam Penilaian',
    className: 'badge-in_review',
    icon: 'fas fa-clock',
  },
  revision: {
    label: 'Perlu Revisi',
    className: 'badge-revision',
    icon: 'fas fa-edit',
  },
  approved: {
    label: 'Disetujui (Approved)',
    className: 'badge-approved',
    icon: 'fas fa-check-circle',
  },
  certificate_issued: {
    label: 'Certificate Terbit',
    className: 'badge-success',
    icon: 'fas fa-certificate',
  },
  rejected: {
    label: 'Ditolak (Rejected)',
    className: 'badge-rejected',
    icon: 'fas fa-times-circle',
  },
};

export const projectStatusMeta = (status) => PROJECT_STATUSES[status] || {
  label: String(status || '-').toUpperCase(),
  className: 'badge-secondary',
  icon: 'fas fa-info-circle',
};
