const MAX_VISIBLE_PAGES = 5;

export function visiblePages(currentPage, totalPages) {
  const first = firstVisiblePage(currentPage, totalPages);
  const last = Math.min(totalPages, first + MAX_VISIBLE_PAGES - 1);

  return Array.from({ length: last - first + 1 }, (_, index) => first + index);
}

function firstVisiblePage(currentPage, totalPages) {
  const centered = currentPage - Math.floor(MAX_VISIBLE_PAGES / 2);
  const lastPossibleStart = Math.max(1, totalPages - MAX_VISIBLE_PAGES + 1);

  return Math.min(Math.max(centered, 1), lastPossibleStart);
}
