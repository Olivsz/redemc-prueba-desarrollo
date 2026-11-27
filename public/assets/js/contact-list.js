import { fetchContacts } from './contacts-api.js';
import { formatCreatedAt, initialsOf } from './format.js';
import { visiblePages } from './pagination.js';

const AVATAR_COLORS = 5;
const HIGHLIGHT_DURATION_MS = 3000;
const EMPTY_MESSAGE = 'Todavía no hay contactos. ¡Cargá el primero!';
const LOAD_ERROR_MESSAGE = 'No pudimos cargar el listado.';

export function createContactList() {
  const $list = $('#contact-list');
  const $status = $('#list-status');
  const $pagination = $('#pagination');
  const $total = $('#contacts-total');

  function load(page, highlightedId = null) {
    $list.addClass('is-loading').attr('aria-busy', 'true');

    fetchContacts(page)
      .done(response => render(response, highlightedId))
      .fail(() => showLoadError(page))
      .always(() => $list.removeClass('is-loading').attr('aria-busy', 'false'));
  }

  function render({ data, meta }, highlightedId) {
    $list.empty().append(data.map(contact => contactItem(contact, contact.id === highlightedId)));
    $status.text(data.length === 0 ? EMPTY_MESSAGE : '');
    $total.text(totalLabel(meta.total));
    $pagination.empty().append(paginationButtons(meta));
    removeHighlightLater();
  }

  function removeHighlightLater() {
    setTimeout(() => $list.find('.is-new').removeClass('is-new'), HIGHLIGHT_DURATION_MS);
  }

  function showLoadError(page) {
    const $retry = $('<button>', { type: 'button', class: 'btn btn-link p-0 align-baseline', text: 'Reintentar' })
      .on('click', () => load(page));

    $status.empty().append(`${LOAD_ERROR_MESSAGE} `, $retry);
  }

  $pagination.on('click', '[data-page]', event => load(Number($(event.currentTarget).data('page'))));

  return { load };
}

function contactItem(contact, isNew) {
  return $('<div>', { class: `contact-item${isNew ? ' is-new' : ''}`, role: 'listitem', 'data-id': contact.id }).append(
    $('<div>', { class: `avatar avatar-${contact.id % AVATAR_COLORS}`, 'aria-hidden': 'true', text: initialsOf(contact.name) }),
    $('<div>', { class: 'contact-body' }).append(
      $('<div>', { class: 'contact-name', text: contact.name }),
      $('<div>', { class: 'contact-details' }).append(
        $('<span>', { class: 'contact-email', text: contact.email }),
        $('<span>', { class: 'contact-phone', text: contact.phone ?? 'Sin teléfono' }),
      ),
    ),
    $('<time>', { class: 'contact-date', datetime: contact.createdAt, text: formatCreatedAt(contact.createdAt) }),
  );
}

function paginationButtons({ page, totalPages }) {
  if (totalPages <= 1) {
    return [];
  }

  return [
    pageButton({ label: 'Anterior', page: page - 1, disabled: page === 1, ariaLabel: 'Página anterior' }),
    ...visiblePages(page, totalPages).map(number => pageButton({ label: String(number), page: number, current: number === page })),
    pageButton({ label: 'Siguiente', page: page + 1, disabled: page === totalPages, ariaLabel: 'Página siguiente' }),
  ];
}

function pageButton({ label, page, disabled = false, current = false, ariaLabel = `Página ${label}` }) {
  return $('<button>', {
    type: 'button',
    class: 'page-button',
    text: label,
    'data-page': page,
    'aria-label': ariaLabel,
    'aria-current': current ? 'page' : null,
    disabled: disabled || current,
  });
}

function totalLabel(total) {
  return total === 1 ? '1 contacto' : `${total} contactos`;
}
