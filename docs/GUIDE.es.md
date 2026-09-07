# Guía de InteractEmbed

## Añadir contenido web

1. Active la edición en una página que admita bloques.
2. Añada **InteractEmbed — Bloque de contenidos interactivos**.
3. Seleccione **Dirección web o iframe**.
4. Pegue una dirección HTTP/HTTPS o el código iframe suministrado por el servicio.
5. Seleccione el formato, el perfil de seguridad y las opciones de visualización, y guarde los cambios.

InteractEmbed reconoce YouTube, Google Docs/Slides/Forms/Sheets, Canva, Genially, Padlet y Wayground. El sitio remoto debe permitir la visualización en un iframe.

Seleccione **Exigir un clic antes de cargar el contenido** cuando el visitante deba decidir antes de establecer una conexión con el proveedor externo. Seleccione **Tarjeta compacta** para mostrar un panel de inicio ligero en una columna estrecha; el reproductor completo se despliega después del clic.

El administrador puede permitir dominios genéricos adicionales en los ajustes del plugin. La opción permisiva para todas las URL genéricas debe reservarse para sitios donde todos los editores de bloques sean de confianza.

## Presentar el contenido

Use **Introducción o descripción accesible** para añadir instrucciones, un objetivo de aprendizaje o una alternativa textual antes del reproductor. Una **Imagen de portada** opcional puede personalizar los modos tarjeta y carga al hacer clic. Su descripción es obligatoria para mantener accesible la información significativa.

## Usar la guía incluida

Seleccione **Guía interactiva incluida** como fuente para mostrar el manual HTML5 incluido con InteractEmbed. La guía utiliza inicialmente el idioma de Moodle para español, francés o inglés y también ofrece su propio selector. Nunca se añade automáticamente ni establece conexiones externas.

## Añadir un paquete HTML5

Seleccione **Paquete HTML5** y suba un único archivo ZIP o los archivos descomprimidos con sus carpetas. Nunca conserve a la vez el ZIP y sus archivos descomprimidos en el selector de Moodle. Lo más sencillo es subir únicamente el ZIP sin usar **Descomprimir**. El archivo de inicio suele ser `index.html`; indique otra ruta cuando sea necesario.

El perfil **Estándar** aísla el paquete de Moodle. El perfil **Paquete de confianza**, reservado al administrador, solo debe utilizarse con archivos bajo su control.

## Reutilizar el reproductor en el mismo Moodle

Active primero **Mostrar el código de inserción** en los ajustes del bloque. Abra después **Código de inserción** debajo del bloque, copie el código y péguelo en una zona HTML de confianza del mismo Moodle. El reproductor conserva la sesión y las reglas de acceso del bloque original. El script incluido ajusta automáticamente la altura.

El código nunca convierte un bloque privado en público ni evita el inicio de sesión o el acceso al curso.

## Administración del sitio

Abra **Administración del sitio > Plugins > Bloques > InteractEmbed** para configurar los valores predeterminados de los bloques nuevos, los tipos de fuentes permitidos, la visualización del código de inserción, la carga obligatoria mediante clic, los dominios adicionales y los límites de seguridad ZIP.

Los valores predeterminados solo inicializan los bloques nuevos y siguen siendo editables. Las políticas del sitio se aplican a todos los bloques. Desactivar un tipo de fuente también hace que los bloques existentes de ese tipo no estén disponibles hasta que se restablezca la política. Desactivar el código de inserción oculta la herramienta de copia, sin romper deliberadamente los códigos iframe que ya se hayan colocado en otras zonas del mismo Moodle.

## Resolver problemas frecuentes

- **No se puede guardar el paquete:** conserve únicamente el ZIP o únicamente los archivos descomprimidos.
- **No se encuentra el archivo inicial:** compruebe las mayúsculas y la ruta completa, por ejemplo `presentacion/index.html`.
- **Una fuente remota queda vacía o es rechazada:** compruebe los permisos del enlace compartido y si el proveedor permite iframes.
- **El contenido no está disponible:** revise las políticas del sitio InteractEmbed, los permisos del contexto y el acceso de invitados cuando el bloque esté en la portada.
