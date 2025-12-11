# Requerimientos de Usuario  Pacha Tour

## Historia 1  Exploración de Departamentos

**User Story:** Como visitante, quiero ver la lista de departamentos de Bolivia para seleccionar uno y explorar sus atractivos.

**Criterios de aceptación:**
1. WHEN el visitante accede a la pantalla principal THEN el sistema SHALL mostrar una lista de departamentos con nombre, imagen principal y enlace al detalle.
2. WHEN el visitante selecciona un departamento THEN el sistema SHALL navegar a la página de detalles del departamento (por slug).
3. WHEN el visitante abre la página de detalle THEN el sistema SHALL mostrar descripción, galería multimedia, coordenadas y un listado de atractivos activos.

---

## Historia 2  Visualización y Gestión de Atractivos

**User Story:** Como visitante quiero ver detalles de un atractivo, y como administrador quiero crear/editar/eliminar atractivos para mantener la información actualizada.

**Criterios de aceptación (visitante):**
1. WHEN el visitante abre la página de un atractivo THEN el sistema SHALL mostrar título, galería multimedia, descripción, tipo de turismo, ubicación y reseñas aprobadas.
2. WHEN el visitante navega entre imágenes o videos THEN el sistema SHALL reproducir/mostrar el medio correctamente.

**Criterios de aceptación (admin):**
1. WHEN un administrador crea o edita un atractivo THEN el sistema SHALL validar campos obligatorios (nombre, departamento, tipo) y guardar medios asociados.
2. WHEN un administrador elimina un atractivo THEN el sistema SHALL eliminarlo o marcarlo inactivo y devolver un mensaje de éxito.

---

## Historia 3  Búsqueda y Filtrado

**User Story:** Como visitante, quiero buscar y filtrar atractivos para encontrar rápidamente opciones que cumplan mis preferencias.

**Criterios de aceptación:**
1. WHEN el usuario escribe en el campo de búsqueda THEN el sistema SHALL ofrecer sugerencias relevantes (autocompletado).
2. WHEN el usuario aplica filtros (departamento, tipo, rango de precio, rating) THEN el sistema SHALL mostrar resultados que cumplan todos los filtros seleccionados.
3. WHEN el usuario aplica orden (por rating o por visitas) THEN el sistema SHALL ordenar los resultados correctamente.

---

## Historia 4  Consultar Tours y Horarios

**User Story:** Como visitante, quiero ver los tours disponibles asociados a un atractivo y sus horarios para poder planificar una reserva.

**Criterios de aceptación:**
1. WHEN el visitante ve el detalle de un atractivo THEN el sistema SHALL listar los tours asociados con nombre, duración y horarios disponibles.
2. WHEN el visitante selecciona un tour THEN el sistema SHALL mostrar el calendario/horarios y la capacidad disponible por horario.

---

## Historia 5  Crear Planificación

**User Story:** Como usuario autenticado, quiero guardar una planificación de visita para organizar mi itinerario antes de reservar oficialmente.

**Criterios de aceptación:**
1. WHEN un usuario autenticado envía una planificación con fecha y número de visitantes THEN el sistema SHALL crear un registro de planificación con estado "pending".
2. WHEN la planificación se crea THEN el sistema SHALL devolver un resumen con detalles (fecha, lugar, contacto, estimado de costo si se suministra).

---

## Historia 6  Reserva de Tour

**User Story:** Como usuario registrado, quiero reservar un tour en una fecha y horario disponibles para asegurar mi cupo.

**Criterios de aceptación:**
1. WHEN el usuario selecciona un tour, horario y cantidad de participantes THEN el sistema SHALL validar disponibilidad y calcular el monto total (precio por persona, comisiones, impuestos si aplica).
2. WHEN la validación es exitosa THEN el sistema SHALL permitir crear la reserva en estado `pending` y devolver un resumen de pago.
3. WHEN el pago se procesa correctamente THEN el sistema SHALL cambiar el estado de la reserva a `confirmed` y generar un comprobante/ticket.

---

## Historia 7  Gestión de Reservas

**User Story:** Como usuario, quiero poder ver, editar o cancelar mis reservas para gestionar mis planes de viaje.

**Criterios de aceptación:**
1. WHEN el usuario accede a "Mis Reservas" THEN el sistema SHALL listar sus reservas con estado, fecha y detalles.
2. WHEN el usuario solicita modificar una reserva dentro de las políticas THEN el sistema SHALL aplicar cambios y recalcular montos.
3. WHEN el usuario cancela una reserva dentro de la política de cancelación THEN el sistema SHALL actualizar el estado a `cancelled` y procesar reembolsos según reglas.

---

## Historia 8  Pagos y Comisiones

**User Story:** Como plataforma, quiero calcular automáticamente las comisiones y permitir registrar pagos para mantener trazabilidad financiera.

**Criterios de aceptación:**
1. WHEN se genera una reserva THEN el sistema SHALL calcular la comisión configurable y guardarla como parte del cálculo del total.
2. WHEN se procesa un pago THEN el sistema SHALL registrar un `Payment` asociado a la reserva, indicando método, referencia y estado.
3. WHEN el pago está registrado como exitoso THEN el sistema SHALL actualizar la reserva a `confirmed` y calcular la porción neta para el operador.

---

## Historia 9  Registro, Inicio de Sesión y Perfil

**User Story:** Como visitante, quiero registrarme e iniciar sesión para acceder a funcionalidades personalizadas; como usuario quiero gestionar mi perfil.

**Criterios de aceptación:**
1. WHEN un visitante se registra con datos válidos THEN el sistema SHALL crear la cuenta y autenticar al usuario.
2. WHEN un usuario inicia sesión con credenciales válidas THEN el sistema SHALL autenticarlo y mantener la sesión segura.
3. WHEN un usuario edita su perfil THEN el sistema SHALL validar cambios y persistirlos.
4. WHEN un usuario solicita recuperación de contraseña THEN el sistema SHALL enviar un enlace de restablecimiento válido.

---

## Historia 10  Favoritos e Itinerario Personal

**User Story:** Como usuario, quiero marcar atractivos como favoritos y agregar elementos a mi itinerario para planificar mis visitas.

**Criterios de aceptación:**
1. WHEN un usuario marca/quita un favorito THEN el sistema SHALL almacenar la preferencia ligada al usuario.
2. WHEN un usuario agrega un elemento al itinerario THEN el sistema SHALL almacenar la fecha y notas y mostrarlo en "Mi Viaje".
3. WHEN el usuario consulta su itinerario THEN el sistema SHALL mostrar los elementos ordenados por fecha.

---

## Historia 11  Reseñas y Moderación

**User Story:** Como usuario, quiero dejar reseñas de atracciones y tours, y como administrador quiero moderarlas antes de publicarlas.

**Criterios de aceptación:**
1. WHEN un usuario con reserva completada envía una reseña THEN el sistema SHALL crear la reseña en estado `pending` o `verified` según la verificación.
2. WHEN un administrador aprueba una reseña THEN el sistema SHALL publicar la reseña y actualizar el rating promedio del elemento.
3. WHEN una reseña es rechazada THEN el sistema SHALL notificar al autor y no mostrarla públicamente.

---

## Historia 12  Administración de Contenido

**User Story:** Como administrador, quiero gestionar departamentos, atractivos, tours, horarios, usuarios y medios para mantener la plataforma actualizada.

**Criterios de aceptación:**
1. WHEN un administrador crea/edita/elimina departamentos, atractivos o tours THEN el sistema SHALL realizar las validaciones y persistir los cambios.
2. WHEN el administrador sube medios THEN el sistema SHALL asociar los archivos a la entidad correspondiente y devolver rutas accesibles.
3. WHEN el administrador accede a reportes THEN el sistema SHALL mostrar métricas básicas (número de reservas, ingresos, reseñas pendientes).

---

## Historia 13  Rol Operador Turístico

**User Story:** Como operador, quiero gestionar mis propios tours y consultar mis ingresos para administrar mi oferta.

**Criterios de aceptación:**
1. WHEN un usuario con rol `operator` crea o edita un tour que le pertenece THEN el sistema SHALL permitir CRUD limitado a sus tours.
2. WHEN el operador consulta sus reportes THEN el sistema SHALL mostrar reservas, ingresos netos y comisiones por período.

---

## Historia 14  Búsqueda Avanzada y Autocompletado (API)

**User Story:** Como cliente (web/app), quiero consumir endpoints de búsqueda y autocompletado para integrar experiencias rápidas de descubrimiento.

**Criterios de aceptación:**
1. WHEN se realiza una solicitud de autocompletado THEN el endpoint SHALL devolver hasta N sugerencias ordenadas por relevancia.
2. WHEN se realiza búsqueda con filtros THEN el endpoint SHALL devolver resultados paginados que respeten los filtros.

---

## Historia 15  Localización / Multi-idioma

**User Story:** Como visitante internacional, quiero elegir el idioma de la interfaz para entender la información.

**Criterios de aceptación:**
1. WHEN el usuario selecciona idioma THEN el sistema SHALL mostrar textos de UI traducidos donde existan traducciones.
2. WHEN no exista traducción de un contenido dinámico THEN el sistema SHALL devolver la versión por defecto (español).

---

## Historia 16  Integración de Mapas

**User Story:** Como visitante, quiero ver la ubicación geográfica de un atractivo en un mapa para planificar la ruta.

**Criterios de aceptación:**
1. WHEN el usuario abre el detalle de un atractivo THEN el sistema SHALL mostrar un mapa centrado en las coordenadas del atractivo.
2. WHEN el usuario solicita ver múltiples atractivos en el mapa THEN el sistema SHALL mostrar marcadores para cada atractivo con resumen en el tooltip.

---

## Historia 17  Notificaciones por Email

**User Story:** Como usuario, quiero recibir notificaciones por email (reserva, pago, cambios) para tener constancia de mis acciones.

**Criterios de aceptación:**
1. WHEN se confirma una reserva THEN el sistema SHALL enviar un email de confirmación al usuario con detalles y comprobante.
2. WHEN cambia el estado de una reserva (confirmada, cancelada) THEN el sistema SHALL notificar al usuario por email.

---

## Historia 18  Acceso API y Tokens

**User Story:** Como desarrollador de clientes API, quiero obtener tokens de acceso para consumir endpoints protegidos.

**Criterios de aceptación:**
1. WHEN un usuario válido solicita un token THEN el sistema SHALL emitir un token válido (scoped) asociado al usuario.
2. WHEN un request con token inválido llega al API THEN el sistema SHALL devolver un error 401.

---

## Historia 19  Gestión de Departamentos (Admin)

**User Story:** Como administrador, quiero crear, editar, eliminar y activar/desactivar departamentos para mantener el catálogo de destinos actualizado.

**Criterios de aceptación:**
1. WHEN el administrador crea un departamento THEN el sistema SHALL validar campos obligatorios (nombre, capital, descripción) y guardar multimedia asociada.
2. WHEN el administrador edita un departamento THEN el sistema SHALL actualizar la información y coordenadas GPS si se proporcionan.
3. WHEN el administrador marca un departamento como inactivo THEN el sistema SHALL ocultarlo del catálogo público.
4. WHEN el administrador accede a estadísticas de departamentos THEN el sistema SHALL mostrar número de atractivos, rating promedio y visitas.

---

## Historia 20  Gestión de Tours y Horarios (Admin)

**User Story:** Como administrador, quiero crear y gestionar tours con sus horarios, capacidades y precios para que los usuarios puedan reservar.

**Criterios de aceptación:**
1. WHEN el administrador crea un tour THEN el sistema SHALL asociarlo a un atractivo y validar horarios y capacidades.
2. WHEN el administrador define horarios THEN el sistema SHALL validar que no haya conflictos y permitir establecer precio por persona y capacidad máxima.
3. WHEN el administrador modifica un tour THEN el sistema SHALL validar que no afecte reservas existentes o permitir ajustes automáticos.
4. WHEN el administrador consulta disponibilidad THEN el sistema SHALL mostrar cupos disponibles por horario en tiempo real.

---

## Historia 21  Dashboard de Métricas (Admin)

**User Story:** Como administrador, quiero ver un dashboard con métricas en tiempo real para monitorear la salud y rendimiento de la plataforma.

**Criterios de aceptación:**
1. WHEN el administrador accede al dashboard THEN el sistema SHALL mostrar usuarios activos, nuevas reservas del día, ingresos totales.
2. WHEN el administrador visualiza gráficos THEN el sistema SHALL mostrar tendencias de registro de usuarios, patrones de reservas, conversión.
3. WHEN el administrador consulta alertas THEN el sistema SHALL mostrar reseñas pendientes de moderación, atracciones inactivas, problemas críticos.

---

## Historia 22  Reportes y Análisis (Admin)

**User Story:** Como administrador, quiero generar reportes detallados de reservas, usuarios, ingresos y desempeño para tomar decisiones informadas.

**Criterios de aceptación:**
1. WHEN el administrador genera un reporte de reservas THEN el sistema SHALL permitir filtrar por fecha, departamento, tipo de tour y estado.
2. WHEN el administrador genera un reporte financiero THEN el sistema SHALL mostrar ingresos, comisiones por operador, métodos de pago populares.
3. WHEN el administrador exporta un reporte THEN el sistema SHALL permitir descargar en CSV o PDF con rango de fechas personalizable.
4. WHEN el administrador analiza performance de atractivos THEN el sistema SHALL mostrar más visitados, mejor calificados y tendencias.

---

## Historia 23  Moderación de Usuarios (Admin)

**User Story:** Como administrador, quiero gestionar usuarios (activar, desactivar, cambiar roles) para mantener control sobre la plataforma.

**Criterios de aceptación:**
1. WHEN el administrador visualiza usuarios THEN el sistema SHALL mostrar lista completa con rol, fecha de registro, actividad reciente.
2. WHEN el administrador desactiva un usuario THEN el sistema SHALL marcar la cuenta como inactiva sin eliminar datos.
3. WHEN el administrador cambia el rol de un usuario THEN el sistema SHALL actualizar permisos inmediatamente (ej: tourist a admin).
4. WHEN el administrador resetea contraseña THEN el sistema SHALL generar enlace temporal para que el usuario establezca nueva contraseña.

---

## Historia 24  Gestión de Medios (Admin)

**User Story:** Como administrador, quiero subir, editar, organizar y eliminar medios (imágenes, videos) para mantener galerías actualizadas.

**Criterios de aceptación:**
1. WHEN el administrador sube medios THEN el sistema SHALL validar formato/tamaño y almacenar en servicio de media (local o cloud).
2. WHEN el administrador edita un medio THEN el sistema SHALL permitir cambiar orden, descripción, nombre o marcarlo como portada.
3. WHEN el administrador asocia medios a una entidad THEN el sistema SHALL mostrar preview y permitir reordenar.
4. WHEN el administrador elimina un medio THEN el sistema SHALL confirmarlo y liberar espacio de almacenamiento.

---

## Historia 25  Confirmación y Estados de Reserva

**User Story:** Como administrador o sistema automático, quiero confirmar reservas y cambiar estados (pending → completed → cancelled) para gestionar el ciclo de vida.

**Criterios de aceptación:**
1. WHEN una reserva se crea THEN el sistema SHALL colocarla en estado `pending` y enviar email de confirmación provisional.
2. WHEN se procesa el pago THEN el sistema SHALL cambiar a estado `confirmed` y liberar el cupo permanentemente.
3. WHEN el usuario completa una visita THEN el sistema SHALL cambiar a estado `completed` y habilitar opción de dejar reseña.
4. WHEN se cancela una reserva dentro de plazo THEN el sistema SHALL cambiar a estado `cancelled` y procesar reembolso automático.

---

## Historia 26  Validación de Disponibilidad en Tiempo Real

**User Story:** Como sistema, quiero validar disponibilidad de tours en tiempo real para evitar sobreventa y conflictos de horarios.

**Criterios de aceptación:**
1. WHEN se intenta crear una reserva THEN el sistema SHALL verificar que haya cupos disponibles (capacidad - reservas confirmadas ≥ participantes).
2. WHEN hay múltiples solicitudes simultáneas THEN el sistema SHALL usar transacciones para garantizar atomicidad.
3. WHEN la disponibilidad cambia THEN el sistema SHALL actualizar instantáneamente en el frontend.
4. WHEN no hay cupos THEN el sistema SHALL mostrar mensaje de "sin disponibilidad" e indicar próximas fechas disponibles.

---

## Historia 27  Cálculo Automático de Precios y Descuentos

**User Story:** Como sistema, quiero calcular precios totales incluyendo comisiones, descuentos por temporada y ajustes automáticos.

**Criterios de aceptación:**
1. WHEN se calcula precio de reserva THEN el sistema SHALL multiplicar precio por persona × participantes + comisiones.
2. WHEN aplica descuento por temporada THEN el sistema SHALL aplicar porcentaje según fecha (ej: 10% en baja temporada).
3. WHEN hay promoción activa THEN el sistema SHALL aplicar descuento adicional si cumple criterios.
4. WHEN se muestra resumen THEN el sistema SHALL detallar: subtotal, comisión, descuentos, impuestos, total final.

---

## Historia 28  Validación y Seguridad de Datos

**User Story:** Como sistema, quiero validar integridad de datos en todos los endpoints y proteger contra accesos no autorizados.

**Criterios de aceptación:**
1. WHEN se recibe una solicitud THEN el sistema SHALL validar campos requeridos, formatos y rangos permitidos.
2. WHEN se accede a recurso protegido THEN el sistema SHALL verificar autenticación y autorización (rol, propiedad).
3. WHEN hay intento de acceso no autorizado THEN el sistema SHALL devolver 403 Forbidden o 401 Unauthorized.
4. WHEN se detecta validación fallida THEN el sistema SHALL devolver mensaje descriptivo con errores específicos.

---
