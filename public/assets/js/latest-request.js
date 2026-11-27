// Ante varios pedidos seguidos, deja actuar solo a la respuesta del último.
// Con una conexión lenta, la respuesta de un pedido viejo puede llegar después y pisar la del nuevo.
export function trackLatestRequest() {
  let latestId = 0;

  function start() {
    const id = ++latestId;

    return callback => (...args) => {
      if (id === latestId) {
        callback(...args);
      }
    };
  }

  return { start };
}
