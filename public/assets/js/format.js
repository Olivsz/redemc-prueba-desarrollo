const URUGUAY_DATE_TIME = new Intl.DateTimeFormat('es-UY', {
  timeZone: 'America/Montevideo',
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hourCycle: 'h23',
});

export function formatCreatedAt(isoDate) {
  const parts = Object.fromEntries(
    URUGUAY_DATE_TIME.formatToParts(new Date(isoDate)).map(({ type, value }) => [type, value]),
  );

  return `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}`;
}

export function initialsOf(name) {
  return name
    .split(' ')
    .slice(0, 2)
    .map(word => word.charAt(0).toUpperCase())
    .join('');
}
