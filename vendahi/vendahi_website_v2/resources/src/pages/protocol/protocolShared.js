/**
 * Shared vocabulary for the Protocol & Requests pages: request types,
 * priorities, statuses and desk actions with their tag colours and i18n keys
 * (Pt_* keys live in i18n/fallback.js; a DB translation wins when present).
 */
export const TYPES = ['complaint', 'request', 'memo', 'letter', 'document', 'enquiry', 'other'];
export const PRIORITIES = ['low', 'normal', 'high', 'urgent'];
export const STATUSES = ['open', 'received', 'acknowledged', 'in_progress', 'approved', 'rejected', 'closed'];
export const ACTIVE_STATUSES = ['open', 'received', 'acknowledged', 'in_progress'];

export const STATUS_COLOR = {
  open: 'blue',
  received: 'cyan',
  acknowledged: 'geekblue',
  in_progress: 'processing',
  approved: 'success',
  rejected: 'error',
  closed: 'default',
};

export const PRIORITY_COLOR = { low: 'default', normal: 'blue', high: 'orange', urgent: 'red' };

export const TYPE_COLOR = {
  complaint: 'volcano', request: 'blue', memo: 'purple', letter: 'geekblue',
  document: 'cyan', enquiry: 'green', other: 'default',
};

/** Timeline dot colour per event action. */
export const EVENT_COLOR = {
  submitted: 'blue', received: 'cyan', acknowledged: 'geekblue', note: 'gray', response: 'green',
  reply: 'green', forwarded: 'purple', reassigned: 'purple', returned: 'orange', approved: 'green',
  rejected: 'red', closed: 'gray', reopened: 'blue', attachment: 'gray', updated: 'gray',
};

/** i18n key + English fallback for every vocabulary word. */
export const LABELS = {
  // types
  complaint: ['Pt_Complaint', 'Complaint'],
  request: ['Pt_Request', 'Request'],
  memo: ['Pt_Memo', 'Memo'],
  letter: ['Pt_Letter', 'Letter'],
  document: ['Pt_Document', 'Administrative document'],
  enquiry: ['Pt_Enquiry', 'Enquiry'],
  other: ['Pt_Other', 'Other correspondence'],
  // priorities
  low: ['Pt_Low', 'Low'],
  normal: ['Pt_Normal', 'Normal'],
  high: ['Pt_High', 'High'],
  urgent: ['Pt_Urgent', 'Urgent'],
  // statuses
  open: ['Pt_Open', 'Awaiting receipt'],
  received: ['Pt_Received', 'Received'],
  acknowledged: ['Pt_Acknowledged', 'Acknowledged'],
  in_progress: ['Pt_In_Progress', 'In progress'],
  approved: ['Pt_Approved', 'Approved'],
  rejected: ['Pt_Rejected', 'Rejected'],
  closed: ['Pt_Closed', 'Closed'],
  active: ['Pt_Active', 'Active'],
  // event actions
  submitted: ['Pt_Ev_Submitted', 'Submitted'],
  note: ['Pt_Ev_Note', 'Internal note'],
  response: ['Pt_Ev_Response', 'Response'],
  reply: ['Pt_Ev_Reply', 'Customer reply'],
  forwarded: ['Pt_Ev_Forwarded', 'Forwarded'],
  reassigned: ['Pt_Ev_Reassigned', 'Reassigned'],
  returned: ['Pt_Ev_Returned', 'Returned to previous desk'],
  reopened: ['Pt_Ev_Reopened', 'Reopened'],
  attachment: ['Pt_Ev_Attachment', 'Documents attached'],
  updated: ['Pt_Ev_Updated', 'Details edited'],
};

/** Desk actions offered on the details page, in the order they appear. */
export const ACTIONS = [
  { key: 'receive', label: ['Pt_Act_Receive', 'Receive'], needsNote: false, type: 'default' },
  { key: 'acknowledge', label: ['Pt_Act_Acknowledge', 'Acknowledge'], needsNote: false, type: 'default' },
  { key: 'note', label: ['Pt_Act_Note', 'Add note'], needsNote: true, type: 'default' },
  { key: 'respond', label: ['Pt_Act_Respond', 'Respond'], needsNote: true, type: 'primary' },
  { key: 'attach', label: ['Pt_Act_Attach', 'Attach documents'], needsNote: false, type: 'default', needsFiles: true },
  { key: 'forward', label: ['Pt_Act_Forward', 'Forward'], needsNote: false, type: 'default', desk: true },
  { key: 'reassign', label: ['Pt_Act_Reassign', 'Reassign'], needsNote: false, type: 'default', user: true },
  { key: 'return', label: ['Pt_Act_Return', 'Return to previous desk'], needsNote: false, type: 'default' },
  { key: 'approve', label: ['Pt_Act_Approve', 'Approve'], needsNote: false, type: 'primary' },
  { key: 'reject', label: ['Pt_Act_Reject', 'Reject'], needsNote: false, type: 'default', danger: true },
  { key: 'close', label: ['Pt_Act_Close', 'Close'], needsNote: false, type: 'default' },
  { key: 'reopen', label: ['Pt_Act_Reopen', 'Reopen'], needsNote: false, type: 'default' },
];

/** Which actions make sense for a status (the server enforces desk rights). */
export function actionsFor(status) {
  if (status === 'closed') return ['reopen'];
  const common = ['note', 'respond', 'attach', 'forward', 'reassign', 'return', 'close'];
  if (status === 'open') return ['receive', 'acknowledge', ...common, 'approve', 'reject'];
  if (status === 'received') return ['acknowledge', ...common, 'approve', 'reject'];
  if (status === 'approved' || status === 'rejected') return ['note', 'respond', 'attach', 'forward', 'return', 'close', 'reopen'];
  return [...common, 'approve', 'reject'];
}

export function makeLabel(t) {
  return key => {
    const entry = LABELS[key];
    if (!entry) return String(key || '').replace(/_/g, ' ');
    const out = t(entry[0]);
    return out === entry[0] ? entry[1] : out;
  };
}

export function fileSize(n) {
  n = Number(n) || 0;
  if (n < 1024) return `${n} B`;
  if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
  return `${(n / 1024 / 1024).toFixed(1)} MB`;
}
