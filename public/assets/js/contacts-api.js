const CONTACTS_URL = '/api/contacts';
const CSRF_HEADER = 'X-CSRF-Token';

export function fetchContacts(page) {
  return $.ajax({
    url: CONTACTS_URL,
    method: 'GET',
    data: { page },
    dataType: 'json',
  });
}

export function createContact(contact) {
  return $.ajax({
    url: CONTACTS_URL,
    method: 'POST',
    contentType: 'application/json; charset=utf-8',
    data: JSON.stringify(contact),
    dataType: 'json',
    headers: { [CSRF_HEADER]: csrfToken() },
  });
}

function csrfToken() {
  return $('meta[name="csrf-token"]').attr('content');
}
