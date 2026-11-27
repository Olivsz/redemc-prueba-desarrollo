# Proceso con IA

## Herramientas

Usé **Claude Code** (la app de escritorio, con el modelo Claude Opus 5.5) durante toda la prueba:

- **Análisis y plan:** leer la letra, detectar requisitos implícitos y armar un plan por fases.
- **Código:** diseño de las capas y desarrollo con TDD. La IA proponía cada test y cada cambio; yo los revisaba, pedía que me explicara lo que no entendía (venía de .NET y no conocía PHP) y aprobaba cada commit.
- **Infraestructura:** Docker y la publicación en Railway, consultando la documentación oficial en lugar de responder de memoria.
- **Pruebas:** la interfaz en el navegador integrado (escritorio y celular) y una prueba de penetración sobre la app publicada.

## Prompts clave

1. **El punto de partida:** *"Leé el PDF y entendé todo. Armá un plan para hacerlo paso a paso, siguiendo Clean Code de Robert Martin, SOLID, GRASP y patrones GoF si hace falta, sin sobreingeniería. Vengo de .NET y REST APIs, no conozco PHP y nunca usé Railway ni publiqué un proyecto."* Salió un plan en 9 fases y una lista de requisitos implícitos: un nombre de solo espacios es vacío, el email se compara sin mayúsculas y un teléfono vacío se guarda como `NULL` para que no choque con el índice único.
2. **Pensar como atacante:** *"Quiero que te comportes como un tester, que intentes romper la aplicación, como si fueses a hackear un poco."* Encontró problemas que yo no habría buscado, como un arreglo de Apache que funcionaba en local y no se aplicaba en Railway, o una carrera en el paginado con conexiones lentas.
3. **Acoplamiento de paquetes:** *"Capaz que no hay acoplamiento a nivel de clases, pero sí a nivel de paquetes. ¿No habría que refactorizar?"* Al medir las dependencias reales apareció que la capa HTTP dependía de la infraestructura, porque el kernel también armaba los objetos. Se movió esa composición a un `CompositionRoot`, como el `Program.cs` de ASP.NET.

## Algo que la IA propuso y corregí

La IA dejó el manejo de errores dentro del controlador: un `try/catch` que traducía las excepciones a códigos HTTP, y además el controlador validaba el token CSRF y leía el JSON a mano. Lo presentó como "equivalente a un exception filter" de ASP.NET, y no lo era. Lo rechacé: pedí controladores finos, el manejo de errores en un solo lugar y DTOs de entrada y salida. Antes del primer commit se rediseñó como el pipeline de ASP.NET: la protección CSRF y el traductor de errores quedaron en el kernel, y cada endpoint quedó en tres pasos (DTO de entrada, caso de uso y DTO de salida).

También aprendí a no dar nada por probado sin verlo: la IA afirmó que el contacto nuevo se resaltaba en amarillo porque el elemento tenía la clase CSS, pero con las animaciones reducidas de Windows no se veía nada. Lo detecté probándolo yo, y se cambió por un color fijo.

## Algo que aprendí

En PHP cada pedido corre en un proceso aparte que arranca de cero y no comparte memoria con los demás. Mi reflejo de .NET para dos envíos simultáneos del mismo email era un `lock` o `async`, pero acá no sirven: la única pieza que comparten todos los procesos es la base de datos. La garantía real es el índice único, y la aplicación traduce ese error a un mensaje claro, igual que se atrapa una violación de clave única en Entity Framework.

También me quedó la importancia de publicar temprano: subir el esqueleto a Railway el segundo día destapó tres problemas que en local no existían, con tiempo de sobra para resolverlos.
