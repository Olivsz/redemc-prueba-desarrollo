const CONTACTS_URL = '/api/contacts';
const CSRF_TOKEN_URL = '/api/csrf-token';
const CSRF_HEADER = 'X-CSRF-Token';
const CSRF_META = 'meta[name="csrf-token"]';
const FORBIDDEN = 403;

export function fetchContacts(page) {
  return $.ajax({
    url: CONTACTS_URL,
    method: 'GET',
    data: { page },
    dataType: 'json',
  });
}

// Si la sesión venció (por ejemplo, porque el servidor se volvió a publicar), el servidor rechaza el envío
// sin guardar nada. Se pide un token nuevo y se reintenta una sola vez, sin que el usuario se entere.
// Cualquier otro error se devuelve tal cual (sigue rechazado) para que el formulario lo muestre.
export function createContact(contact) {
  return postContact(contact).catch(request => (isExpiredSession(request) ? retryWithANewToken(contact) : request));
}

function postContact(contact) {
  return $.ajax({
    url: CONTACTS_URL,
    method: 'POST',
    contentType: 'application/json; charset=utf-8',
    data: JSON.stringify(contact),
    dataType: 'json',
    headers: { [CSRF_HEADER]: $(CSRF_META).attr('content') },
  });
}

function retryWithANewToken(contact) {
  return renewCsrfToken().then(() => postContact(contact));
}

function renewCsrfToken() {
  return $.getJSON(CSRF_TOKEN_URL).then(response => $(CSRF_META).attr('content', response.token));
}

function isExpiredSession(request) {
  return request.status === FORBIDDEN;
}
