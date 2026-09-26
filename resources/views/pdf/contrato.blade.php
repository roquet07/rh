<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Contrato individual de trabajo</title>
    <style>
        @page { margin: 45pt 46pt 52pt; }
        body { font-family: Helvetica, sans-serif; font-size: 10pt; color: #111; line-height: 1.3; }
        p { text-align: justify; margin: 0 0 10px; orphans: 3; widows: 3; }
        h1, h2 { text-align: center; font-size: 12pt; margin: 18px 0; page-break-after: avoid; }
        .closure { page-break-inside: avoid; }
        .new-section { page-break-before: always; }
        .multiline { white-space: pre-line; text-align: justify; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        .beneficiarios { margin: 16px 0; }
        .beneficiarios th, .beneficiarios td { border: 1px solid #777; padding: 8px; text-align: left; overflow-wrap: break-word; }
        .beneficiarios thead { display: table-header-group; }
        .signatures { margin-top: 48px; page-break-inside: avoid; }
        .signatures td { width: 50%; text-align: center; padding: 14px 12px 22px; border-top: 1px solid #444; }
    </style>
</head>
<body>
<p>CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO {{ mb_strtoupper($contrato->tipo) }} QUE CELEBRAN EL {{ mb_strtoupper($fechaCelebracionTexto) }} POR UNA PARTE {{ $empresa->razon_social }}, REPRESENTADO POR EL C. {{ $empresa->representante_legal }}, A QUIEN EN LO SUCESIVO SE LE DENOMINARA "LA EMPRESA" Y POR LA OTRA EL C. {{ $empleado->nombreCompleto() }}, POR SU PROPIO DERECHO, A QUIEN EN LO SUCESIVO SE LE DENOMINARA “EL TRABAJADOR”, AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS:</p>

<h2>DECLARACIONES</h2>

<p>A. DECLARA “LA EMPRESA”:</p>

<p>I. Ser una sociedad mexicana, constituida conforme a la Ley General de Sociedades Mercantiles, como lo demuestra con la {{ $empresa->escritura_constitutiva }} y para los efectos de este contrato se señala como su domicilio legal ubicado en {{ $empresa->domicilio_fiscal }}.</p>

<p>II. Está inscrito en el Registro Público de Comercio bajo el Registro Federal de Contribuyentes con el número {{ $empresa->rfc }}.</p>

<p>III. Que, para la consecución de sus fines, además de sus programas y presupuestos aprobados, realiza otra serie de actividades concretas, mediante la celebración de acuerdos, convenios o contratos con los sectores público, social y privado.</p>

<p>IV. Que su Representante Legal C. {{ $empresa->representante_legal }}, cuenta con las facultades necesarias para celebrar el presente Contrato y acredita su personalidad dentro de la {{ $empresa->poder_representante }}.</p>

<p>V. Que requieren para la realización del contrato señalado en la declaración anterior, los servicios del {{ mb_strtoupper($contrato->puesto->nombre) }} para llevar a cabo las acciones materia de este contrato, consistentes en: {{ rtrim($contrato->funciones, '. ') }}.</p>

<p>VI. Para efectos de este contrato, se señala como su domicilio legal, ubicado en {{ $empresa->domicilio_fiscal }}.</p>

<p>B. DECLARA “EL TRABAJADOR”:</p>

<p>I. Ser una persona física en pleno ejercicio de sus derechos, con capacidad de obligarse en términos de lo establecido en el presente contrato.</p>

<p>II. Ser de nacionalidad {{ $contrato->nacionalidad }}, con {{ $edad }} años de edad, sexo {{ $contrato->sexo }}, estado civil {{ $empleado->estado_civil }}, que se encuentra inscrito en el Registro Federal de Contribuyentes, bajo la clave {{ $empleado->rfc }}, que cuenta con la siguiente Clave Única de Registro de Población {{ $empleado->curp }}, que tiene el siguiente número de seguridad social {{ $empleado->nss ?: 'N/A' }} y señala como su domicilio el ubicado en {{ $domicilioEmpleado }}.</p>

<p>III. Contar con los conocimientos, la capacidad, aptitudes y experiencia necesaria que se requieren para llevar a cabo la labor de {{ mb_strtoupper($contrato->puesto->nombre) }} requerida por “LA EMPRESA”.</p>

<p>C. DECLARAN “LAS PARTES”:</p>

<p>I. Que no existe dolo, violencia, lesión, error ni cualquier otro vicio.</p>

<p>Hechas las declaraciones anteriores, ambas partes convienen celebrar el presente contrato, de conformidad con las siguientes:</p>

<h2>CLÁUSULAS</h2>

<p><strong>PRIMERA. - TIPO Y PLAZO DE CONTRATO.</strong>
@if ($contrato->tipo === 'determinado')
El presente Contrato Individual de Trabajo se celebra por tiempo determinado por {{ $contrato->duracion_meses }} {{ $contrato->duracion_meses === 1 ? 'mes' : 'meses' }}, del {{ $contrato->fecha_inicio->format('d/m/Y') }} al {{ $contrato->fecha_fin->format('d/m/Y') }}, así mismo no podrá haber prórroga automática por el simple transcurso del tiempo y terminará sin necesidad de darse aviso entre las partes, teniendo derecho al finiquito de ley.
@else
El presente Contrato Individual de Trabajo se celebra por tiempo indeterminado, con fecha de inicio el {{ $contrato->fecha_inicio->format('d/m/Y') }}, sin fecha de terminación.
@endif
</p>

<p>SEGUNDA. -OBJETO DE CONTRATO. “EL TRABAJADOR” conviene en prestar sus servicios personales, subordinados a “LA EMPRESA”, con la categoría de {{ $contrato->puesto->nombre }}, realizando actividades tales como:</p>

<div class="multiline">{{ $contrato->funciones }}</div>

<p>Sujetándose a la dirección, vigilancia o instrucciones que reciba de “LA EMPRESA”. Asimismo, “EL TRABAJADOR” conviene expresamente en que desempeñará cualquier otra actividad que “LA EMPRESA”, de acuerdo con las necesidades del servicio, sus conocimientos, habilidades, experiencia, capacitación y grado de confianza depositada, siempre y cuando ésta sea conexa a las actividades encomendadas al puesto asignada.</p>

<p>TERCERA. - DOMICILIO LABORAL. “EL TRABAJADOR” conviene en que prestará sus servicios materia de este contrato en {{ $contrato->lugar_trabajo }}. Para el caso de que por necesidades del servicio “EL TRABAJADOR” tenga que ser trasladado a cualquier otro domicilio ubicado dentro de {{ $empresa->ciudad_firma }} o en el interior de la República, “EL TRABAJADOR” manifiesta expresamente su conformidad.</p>

<p>CUARTA. - JORNADA LABORAL. Las partes convienen en que “EL TRABAJADOR” desempeñará sus labores en una jornada de trabajo {{ $contrato->jornada }} semanal de {{ $contrato->horas_semanales }} horas, con horario {{ $contrato->horario_trabajo }}, estando conformes las partes en fijar la jornada diaria de trabajo de conformidad con lo dispuesto en el artículo 59 de la Ley Federal el Trabajo, contando con {{ $contrato->descanso_minutos }} minutos para descansar o tomar alimentos fuera de las instalaciones de la “LA EMPRESA”; disfrutando {{ $contrato->dia_descanso }} de cada semana como día de descanso semanal, otorgando expresamente su consentimiento para que “LA EMPRESA” ajuste los horarios y el día de descanso conforme a las necesidades laborales o la naturaleza de sus funciones. Las partes convienen en forma expresa en que para que “EL TRABAJADOR” pueda laborar jornada extraordinaria conforme a lo previsto por los artículos 66, 67, 68 y demás relativos y aplicables de la Ley Federal de Trabajo. Debiendo recibir la autorización de las horas extras por escrito de “LA EMPRESA” por conducto de sus representantes autorizados, ya que en caso contrario no se reconocerá tiempo extraordinario alguno.</p>

<p>QUINTA. – SALARIO Y FORMA DE PAGO. Las partes convienen en que “EL TRABAJADOR” percibirá por el trabajo prestado objeto de este Contrato la cantidad de ${{ number_format((float) $contrato->salario_mensual, 2) }} ({{ $salarioLetras }}) como salario mensual, previo a deducciones legales y a prestaciones adicionales que se encuentran vigentes al momento del inicio del presente contrato. El que le será cubierto en forma {{ $contrato->periodicidad_pago }}, los días {{ $contrato->dias_pago }}, suma en moneda nacional en el que se incluye el pago de los séptimos días, correspondiente el día de descanso obligatorio previsto por el presente contrato y la Ley Federal del Trabajo.</p>

<p>“EL TRABAJADOR” está de acuerdo en que sus salarios le sean cubiertos mediante {{ $contrato->forma_pago }}, con la periodicidad y los días señalados en el párrafo que antecede. Asimismo las partes convienen que se podrá sustituir los recibos de pago impresos con firma autógrafa, con los recibos de pago contenidos en los Comprobantes Fiscales Digitales por Internet (CFDI), de acuerdo con lo establecido por el Artículo 101 de la Ley Federal del Trabajo, siendo la totalidad de los salarios ordinarios y extraordinarios devengados a que tenga derecho, conviniéndose en que la firma material o electrónica, implicará un finiquito total hasta la fecha del recibo correspondiente. Igualmente “EL TRABAJADOR” está de acuerdo en cumplir con las leyes que inciden en la relación laboral por lo que autoriza a “LA EMPRESA” para que le retenga y entere a las Dependencias correspondientes, el impuesto, cuota obrera, INFONAVIT, SAR y entre otras prestaciones, así como en su caso lo estipulado en el artículo 110 de la Ley Federal del Trabajo.</p>

<p>SEXTA. - BENEFICIARIOS. En términos de lo dispuesto por el artículo 25, fracción X, en relación con el artículo 501 de la Ley Federal el Trabajo “EL TRABAJADOR” manifiesta que, en expresión de mi libre y espontánea voluntad, designo como beneficiarios a las siguientes personas, a efecto de que reciban el pago de las prestaciones laborales a que tenga derecho:</p>

<table class="beneficiarios"><thead><tr><th>NOMBRE COMPLETO</th><th>PARENTESCO</th><th>PORCENTAJE</th></tr></thead><tbody>
@foreach ($contrato->beneficiarios as $beneficiario)
<tr><td>{{ $beneficiario['nombre'] }}</td><td>{{ $beneficiario['parentesco'] }}</td><td>{{ number_format((float) $beneficiario['porcentaje'], 2) }}%</td></tr>
@endforeach
</tbody></table>

<p>En caso de que se designen beneficiarios menores de edad, los derechos los ejercerá quien acredite legalmente que cuenta con la tutela del menor. Esta designación, deja sin efectos cualquier otra señalada con anterioridad a la presente fecha y que podré sustituirla por escrito, en cualquier momento que lo estime necesario, haciéndose efectiva la de fecha más reciente.</p>

<p>SÉPTIMA. - CONTROL DE ASISTENCIA. “EL TRABAJADOR” el trabajador deberá apegarse al registro de asistencia y a lo establecido en el reglamento interior de trabajo.</p>

<p>En razón de lo anterior, con la firma del presente contrato “EL TRABAJADOR” autoriza a “LA EMPRESA” la toma, manejo y uso de datos personales, así como biométricos (huella digital y rostro para reconocimiento facial) con el fin de dar cumplimiento a la presente cláusula, comprometiéndose la empresa en resguardar y manejar dicha información conforme a lo previsto en el Aviso de Privacidad que anexa al presente contrato, en cuanto a los términos de la Ley Federal de Protección de Datos en Posesión de los Particulares.</p>

<p>OCTAVA. - OBLIGACIONES.</p>

<p>A. EL TRABAJADOR se obliga de manera enunciativa mas no limitativa a:</p>

<p>I. Someterse a los exámenes o reconocimientos médicos que acuerde “LA EMPRESA” y a poner en práctica las medidas profilácticas y de higiene que la misma o las autoridades del ramo acuerden, estando sujetas ambas partes a las disposiciones que establezcan las normas oficiales mexicanas en materia de seguridad, salud y medio ambiente de trabajo, así como las que indiquen los patrones para su seguridad y protección personal.</p>

<p>II. Realizar los planes y programas de capacitación y adiestramiento establecidos en el centro de labores.</p>

<p>III. Cumplir en todas y cada una de sus partes las disposiciones contenidas en el presente contrato, Ley Federal del Trabajo, el Reglamento Interior de Trabajo, políticas empresariales y demás disposiciones establecidas, mismas que en este acto manifiesta conocer, prestando sus servicios en los términos establecidos en los mismos, bajo la dirección de “LA EMPRESA” o sus representantes a cuya autoridad estará subordinado, con la intensidad, cuidado y esmero apropiados en relación con el desempeño de sus labores.</p>

<p>IV. En que “LA EMPRESA” monitoree el uso de las herramientas de trabajo que se le proporcionen para el cumplimiento de sus funciones. Esto de manera enunciativa y no limitativamente: revisar sin previo aviso el contenido del equipo de cómputo proporcionado por “LA EMPRESA” revisar los correos electrónicos que se envíen y/o reciban a través del sistema de “LA EMPRESA”, pudiendo incluso esta última utilizar sistemas automatizados que respalden la comunicación electrónica de “EL TRABAJADOR” de manera simultánea al enviar o recibir correos o mensajes; revisar las páginas de Internet que “EL TRABAJADOR” visita utilizando el equipo de “LA EMPRESA” bloquear sitios específicos de Internet o limitar el tiempo que “EL TRABAJADOR” puede hacer uso del mismo durante las horas de trabajo; monitorear los mensajes en el buzón de voz, los chats corporativos, su escritorio y/u oficina. El incumplimiento de las obligaciones establecidas en el presente inciso será causal de rescisión de la relación laboral sin responsabilidad para “LA EMPRESA” en los términos del artículo 47, fracción XV, de la Ley Federal de Trabajo. Asimismo, “EL TRABAJADOR” responderá e indemnizará a “LA EMPRESA” por los daños y perjuicios que le llegaré a ocasionar en virtud del incumplimiento de sus obligaciones en materia de protección de datos personales, independientemente de las sanciones penales que en su caso procedieran con base en la normativa aplicable.</p>

<p>V. Cumplir con el contrato de confidencialidad firmado, así como guardar escrupulosamente toda la información de la que tenga conocimiento con motivo de las actividades que desempeñará, así como los secretos técnicos, comerciales y de fabricación de los productos en cuya elaboración intervenga o influya directa o indirectamente y de los cuales tenga conocimiento en razón del trabajo que realiza, así como los asuntos administrativos reservados o no, cuya divulgación pueda causar perjuicios a “LA EMPRESA”</p>

<p>VI. Conservar en buen estado y dar el mejor uso a los implementos de trabajo y bienes del patrón, salvo el desgaste natural de los implementos de trabajo, quedando bajo la responsabilidad del TRABAJADOR cualquier faltante de equipo o material que esté a su disposición.</p>

<p>VII. Al término de la relación de trabajo por cualquier causa, “EL TRABAJADOR” está obligado a hacer la entrega de los instrumentos de trabajo, incluido el material en el que conste información, tales como documentos, medios electrónicos, magnéticos, discos ópticos, microfilms, películas y otros instrumentos similares relacionados en forma directa o indirecta con la información confidencial que hubiera elaborado, recopilado o hubiera tenido acceso con motivo de su trabajo, quedando prohibida su sustracción.</p>

<p>B. “LA EMPRESA” se obliga a:</p>

<p>I. Capacitar y adiestrar a “EL TRABAJADOR” y éste está obligado a recibir la capacitación y adiestramiento contenidos en los planes y programas de capacitación establecidos en “LA EMPRESA” de conformidad con lo establecido en el artículo 153-A y demás aplicables de la Ley Federal del Trabajo.</p>

<p>II. Cumplir con todas las partes del presente contrato.</p>

<p>III. Apegarse y dar cumplimiento a las Leyes, Normativas, Políticas, Reglamentos y demás Disposiciones Legales en materia Laboral, Seguridad Social y Empresariales aplicables a la naturaleza de “LA EMPRESA”.</p>

<p>IV. Proporcionar los recursos humanos y materiales indispensables para el desarrollo de las actividades de “EL TRABAJADOR”, mismos que deberá regresar al finalizar su contrato.</p>

<p>NOVENA. - SEGURIDAD SOCIAL. Ambas partes se obligan a someterse a las disposiciones que en materia de seguridad social establece la Ley del Seguro Social y los preceptos relativos y aplicables de la Ley Federal del Trabajo.</p>

<p>DÉCIMA. - PROHIBICIONES DE “EL TRABAJADOR”.</p>

<p>A. Divulgar ni dará a conocer en ningún tiempo, durante o después de la época de prestación de sus servicios, directa o indirectamente a cualquier persona o empresa, sin consentimiento previo y por escrito de “LA EMPRESA”, ningún conocimiento o información que haya adquirido o adquiera durante el transcurso o con motivo de su trabajo, o ningún negocio en el cual “LA EMPRESA” haya estado o haya podido estar relacionada o interesada.</p>

<p>B. No revelar de forma alguna ya sea escrita o verbal, pública o privada, manual o electrónica, así como a mantener absoluta secrecía y guardar absoluta confidencialidad respecto de las políticas, procedimientos, reglamentos, contratos, información técnica, de logística y sobre los productos, planes de negocio, listas de clientes, diseños, planes y metas, campañas publicitarias, planes de mercadeo, información y estrategias de comercialización y ventas, estrategias comerciales de carácter nacional o internacional, procesos industriales convenios y/o cualquier otro documento del que tuviere conocimiento con motivo del desempeño de sus servicios. Quedan incluidos de manera enunciativa y no limitativa, los informes, reportes, manuales, borradores de documentos, material literario, artístico, programas de cómputo, rutinas, aplicaciones, herramientas, cintas, procesos, logaritmos, especificaciones técnicas, modelos, sistemas de calidad y demás relacionado con motivo de la prestación de servicios materia del presente contrato</p>

<p>C. Dar noticias o información respecto de los depósitos, servicios o cualquier otro tipo de operaciones, sino al depositante, deudor titular o beneficiario que corresponda, a sus representantes legales, quienes tengan otorgado un poder en su favor para disponer de la cuenta o para intervenir en la operación o servicio correspondiente, salvo cuando las pidieran, (I) la autoridad judicial competente en virtud de la providencia dictada en juicio en el que el titular sea parte; y (II) las autoridades hacendarias federales por conducto de la Comisión Nacional Bancaria y de Valores.</p>

<p>D. Laborar de una manera directa o indirecta con cualquier empresa considerada como competencia de “LA EMPRESA” o cualquiera de sus filiales y/o crear una empresa con el mismo giro comercial al de “LA EMPRESA”.</p>

<p>EL TRABAJADOR” conviene que estas obligaciones subsistirán aun cuando se hubiere dado por terminada la relación laboral con “LA EMPRESA”, por un periodo de 5 (cinco) años.</p>

<p>DÉCIMA PRIMERA. USO DE IMAGEN PERSONAL. “EL TRABAJADOR” autoriza a “LA EMPRESA” el uso de la imagen personal en fotografías o videograbaciones para campañas, promocionales y demás material de apoyo que se consideren pertinentes para difusión y promoción de “LA EMPRESA”, y que se distribuyan en el país o en el extranjero por cualquier medio, ya sea impreso, electrónico o de otro tipo. Asimismo, con fundamento en los artículos 86, 87 y 88 de la Ley Federal del Derecho de Autor, se autoriza de manera voluntaria y totalmente gratuita para que “LA EMPRESA” pueda reproducir, transmitir, retransmitir, mostrar públicamente, crear otras obras derivadas de la imagen del TRABAJADOR en las campañas de promoción que se realice por cualquier medio, así como la fijación de la citada imagen en proyecciones gráficas, textos, filminas y todo el material suplementario de las promociones y campañas, estableciendo que se utilizará única y exclusivamente para los fines señalados. En ese sentido, autoriza el TRABAJADOR el uso de su nombre y cualquier comentario que pudiese haber hecho mientras se grababa el video y que tal comentario sea editado con los fines señalados. De acuerdo con esto el TRABAJADOR renuncia a todo derecho de inspeccionar o aprobar las secuencias de video grabación o fotografía. Autoriza el T R A B A J A D O R que la imagen que sea utilizada durante el tiempo que “LA EMPRESA” considere adecuado; no obstante, dicha autorización podrá ser revocada mediante escrito dirigido al Departamento Jurídico de “LA EMPRESA”.</p>

<p>DECIMA SEGUNDA. - PROPIEDAD INTELECTUAL Y/O DERECHOS DE AUTOR. Respecto de las invenciones, modelos de utilidad, diseños industriales, etc., que realice “EL TRABAJADOR”, éste tendrá derecho a que su nombre figure como autor de la invención. Si “EL TRABAJADOR” se dedica a trabajos de investigación o de perfeccionamiento de los procedimientos utilizados en “LA EMPRESA” por cuenta de ésta, la propiedad de la invención y el derecho a la explotación de la patente corresponderán a “LA EMPRESA”. “LA EMPRESA” podrá gestionar cualquier tipo de protección o registro respecto de los productos del trabajo, ante las autoridades competentes.</p>

<p>Las partes convienen que en todo tipo de trabajo en el que “EL TRABAJADOR” intervenga, realice, elabore o participe y con motivo de ello, tenga acceso a papeles, cifras, estudios, técnicas de producción o cualquier otro dato relacionado con la labor que desempeñe, referente a “LA EMPRESA”, son propiedad de ésta, incluyendo los derechos de autor y de manera específica los derechos patrimoniales sobre dichos trabajos en los términos de las disposiciones legales aplicables.</p>

<p>DECIMA TERCERA. - TERMINACIÓN ANTICIPADA. Las partes convienen en que “LA EMPRESA” podrá rescindir sin su responsabilidad el presente contrato, cuando “EL TRABAJADOR” incurra en las causales y en los plazos previstas en la Ley Federal Del Trabajo, y en el Reglamento Interior de Trabajo, adicionales a los previstos se establecen los siguientes:</p>

<p>I. Si “EL TRABAJADOR” engaña con certificados o referencias falsas en los que se atribuya al TRABAJADOR capacidad, aptitudes o facultades requeridas para desempeñar el trabajo contratado y de las cuales carezca.</p>

<p>II. La divulgación de información (cualquiera que sea la naturaleza de la misma) será considerada como práctica desleal a “LA EMPRESA” y pueden dar motivo, según la gravedad de la falta hasta la rescisión del presente contrato sin responsabilidad de “LA EMPRESA”, así como a otras acciones de carácter civil o penal.</p>

<p>III. El incumplimiento del contrato de confidencialidad causará rescisión inmediata sin responsabilidad para “LA EMPRESA”, y reservándose ésta el ejercicio en contra del TRABAJADOR las acciones penales, civiles o labores que estime pertinentes.</p>

<p>IV. Los actos de discriminación motivada por origen étnico o nacional, el género, la edad, las discapacidades, la condición social, las condiciones de salud, la religión, las opiniones, las preferencias sexuales, el estado civil o cualquier otra que atente contra la dignidad humana y tenga por objeto anular o menoscabar los derechos y libertades de las personas puede ser motivo de terminación de contrato.</p>

<p>DÉCIMA CUARTA. - DOMICILIOS PARA RECIBIR NOTIFICACIONES. Ambas partes señalan como domicilios para oír y recibir notificaciones son los establecidos en el capítulo de “DECLARACIONES”. Debiendo “EL TRABAJADOR” dar aviso oportuno por escrito en caso de cambio de domicilio a “LA EMPRESA”.</p>

<p>DÉCIMA QUINTA. - JURISDICCIÓN. Para la interpretación y cumplimiento del presente contrato, las partes se someten a la jurisdicción y competencia de los Tribunales de {{ $empresa->entidad_jurisdiccion }}, así como a las disposiciones contenidas en el Código Civil vigente para la {{ $empresa->entidad_jurisdiccion }}, haciendo renuncia expresa de cualquier otra jurisdicción a la que pudiesen tener derecho por razón de domicilio presente o futuro u otra cause generadora de competencia.</p>

@if ($contrato->clausulas_adicionales)
<h2>CLÁUSULAS ADICIONALES</h2><div class="multiline">{{ $contrato->clausulas_adicionales }}</div>
@endif

<div class="closure">
<p>Leído el presente se firma en {{ $empresa->ciudad_firma }}, LAS PARTES que intervienen manifiestan que es su voluntad la que ha sido libremente expresada y que su consentimiento no se encuentra viciado por dolo, error, mala fe o cualquier otro vicio de la voluntad, que están en completo entendimiento de los efectos y alcance del presente y de que toda la información incluida y adjunta en este es correcta y completa, estampan su firma al margen y al calce para todos los efectos legales que en derecho hubiere lugar.</p>

@include('pdf.contrato-firmas')
</div>

<h1 class="new-section">AVISO DE PRIVACIDAD SIMPLIFICADO</h1>

<h2>PARA CLIENTES, PROVEEDORES Y EMPLEADOS</h2>

<p>{{ $empresa->razon_social }} con domicilio en {{ $empresa->domicilio_fiscal }}; de conformidad con lo dispuesto en la Ley Federal de Protección de Datos Personales en Posesión de los Particulares hace de su conocimiento el presente Aviso de Privacidad:</p>

<p>La utilización de la información proporcionada por los usuarios de {{ $empresa->razon_social }} través del uso, desarrollo, actividad e interacción que tenga con los servicios, sistema, sitio web que ofrece la empresa, o la proporcionada a terceros contratados por ésta misma; será recopilada, resguardada y protegida conforme a lo establecido en la Ley Federal de Protección de Datos Personales en Posesión de Particulares en sus Artículos 16, 18, 19, 22, 24, 25, 26 y los demás relativos correspondientes; garantizando a los usuarios en todo tiempo, los principios de licitud, consentimiento, calidad, finalidad, lealtad, proporcionalidad y responsabilidad sobre el derecho de protección de datos personales.</p>

<p>Los datos personales que se soliciten por parte del personal autorizado de {{ $empresa->razon_social }}, serán obligatorios e indispensables para su trámite, por lo que una negativa a proporcionarlos será motivo de considerar por cancelada su solicitud.</p>

<p>I. Finalidades y especificaciones de los datos personales que se recaban</p>

<p>Los datos personales recabados por {{ $empresa->razon_social }}, que se encuentran contenidos en los formatos de registro de estas actividades son los siguientes:</p>

<p>a. Generales</p>

<p>b. Identificación de Datos Personales: Comprende nombre del usuario, comentarios en blog, correo electrónico, dirección física, datos bancarios, sexo, CURP, número de identificación, fecha de nacimiento, datos del beneficiario (en su caso); etc. Dichos datos no serán publicados de forma alguna y sólo servirán para control, contacto y seguimiento de usuario por parte de {{ $empresa->razon_social }}</p>

<p>c. Información de contratación y pago de utilidades, comprende todos los datos personales, documentación solicitada, datos bancarios y de cuenta, formatos firmados y datos de contacto que sean necesarios para la contratación de los servicios que proporciona o bien para cualquiera de sus adendum.</p>

<p>d. Datos de información patrimonial, financieros, laborales, académicos, tránsito y migratorios, sobre procedimientos administrativos seguidos en forma de juicio y/o jurisdiccionales, otros sensibles como salud, vida sexual, características físicas, hábitos, afiliaciones sindicales, biométricos (huellas digitales, voz, imagen videograbada, firma digital, reconocimiento facial) y geolocalización de dispositivos, por lo que solicitamos su consentimiento a través del presente Aviso de Privacidad.</p>

<p>II. Uso de la información proporcionada</p>

<p>a. Lo anteriormente descrito, con el propósito de identificación, generación de base de datos matriz, seguimiento de pago de utilidades, publicaciones, historial de visita, contenidos relevantes, material digital, modo de contacto, vistas, retroalimentación, funciones y anuncios; relacionados con el contenido ofrecido en {{ $empresa->razon_social }}</p>

<p>b. De manera adicional, utilizaremos su información personal para las siguientes finalidades de {{ $empresa->razon_social }}, las cuales nos permiten proveer, mantener y mejorar los servicios de {{ $empresa->razon_social }}, tales como: Mercadotecnia publicitaria. Envío de comunicaciones sobre ofertas y nuevos productos y/o servicios proporcionados. Envío de información y ofertas comerciales (electrónicas y físicas) sobre productos y/o servicios ofrecidos o producidos por terceros. Información y Prestación de Servicios. Estrategia de Marketing. Estrategia de Ventas. Envío de información sobre eventos sociales actividades recreativas, educacionales o culturales. Prospección comercial presente y futura de la Empresa. Estadísticas. Actualizaciones y contenido relevante. Tráfico web; y Rendimiento del Sitio Web y sistema. Control de Asistencia</p>

<p>Los datos personales descritos en el párrafo anterior serán recabados con los siguientes fines</p>

<p>Recibir, registrar, tramitar y atender las solicitudes de acceso a la información que se reciban de ejercicio de derechos ARCO. Proporcionar el servicio solicitado. Con fines estadísticos y de evaluación. Con fines de comprobación de los recursos financieros aplicados en su caso.</p>

<p>De manera adicional, los datos personales que nos proporcione podrán ser utilizados en Informes sobre el servicio brindado, promoción de eventos y actividades institucionales.</p>

<p>III. Los mecanismos y medios disponibles para que pueda manifestar su negativa al tratamiento de sus datos personales.</p>

<p>En el ejercicio de la protección de sus datos personales, usted como titular podrá manifestar su negativa en el tratamiento de sus datos personales, mediante un escrito libre dirigido a la Unidad de Transparencia, con domicilio en {{ $empresa->domicilio_fiscal }} México o al correo electrónico {{ $empresa->correo_privacidad }} con horario de atención de {{ $empresa->horario_privacidad }}.</p>

<p>Este aviso de privacidad podrá ser modificado por {{ $empresa->razon_social }}, dichas modificaciones serán oportunamente informadas a través de correo electrónico, teléfono, o cualquier otro medio de comunicación que {{ $empresa->razon_social }}, determine para tal efecto.</p>

<p>IV. El sitio donde podrá consultar el aviso de privacidad integral es en la siguiente dirección electrónica: {{ $empresa->url_aviso_privacidad }}</p>

<p class="new-section">CONTRATO DE CONFIDENCIALIDAD QUE CELEBRAN, POR UNA PARTE, {{ $empresa->razon_social }} A LA QUE EN LO SUCESIVO SE LE DENOMINARÁ “LA EMPRESA” DEBIDAMENTE REPRESENTADA POR EL C. {{ $empresa->representante_legal }} Y POR LA OTRA EL C. {{ $empleado->nombreCompleto() }}, A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ COMO “EL RECEPTOR”, CONFORME AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS:</p>

<h2>DECLARACIONES</h2>

<p>A. DECLARA “LA EMPRESA”:</p>

<p>I. Ser una sociedad mexicana, constituida conforme a la Ley General de Sociedades Mercantiles, como lo demuestra con la {{ $empresa->escritura_constitutiva }} y para los efectos de este contrato se señala como su domicilio legal ubicado en {{ $empresa->domicilio_fiscal }}.</p>

<p>II. Está inscrito en el Registro Público de Comercio bajo el Registro Federal de Contribuyentes con el número {{ $empresa->rfc }}.</p>

<p>III. Que, para la consecución de sus fines, además de sus programas y presupuestos aprobados, realiza otra serie de actividades concretas, mediante la celebración de acuerdos, convenios o contratos con los sectores público, social y privado.</p>

<p>IV. Que su Representante Legal C. {{ $empresa->representante_legal }}, cuenta con las facultades necesarias para celebrar el presente Contrato y acredita su personalidad dentro de la {{ $empresa->poder_representante }}.</p>

<p>V. Su objeto social contempla la realización de actos como el que se consigna en este documento.</p>

<p>B. Del “EL RECEPTOR:</p>

<p>A. Ser una persona física en pleno ejercicio de sus derechos, con capacidad de obligarse en términos de lo establecido en el presente contrato.</p>

<p>B. Ser de nacionalidad {{ $contrato->nacionalidad }}, con {{ $edad }} años de edad, sexo {{ $contrato->sexo }}, estado civil {{ $empleado->estado_civil }}, que se encuentra inscrito en el Registro Federal de Contribuyentes, bajo la clave {{ $empleado->rfc }}, que cuenta con la siguiente Clave Única de Registro de Población {{ $empleado->curp }}, que tiene el siguiente número de seguridad social {{ $empleado->nss ?: 'N/A' }} y señala como su domicilio el ubicado en {{ $domicilioEmpleado }}.</p>

<p>C. Declaran LAS PARTES:</p>

<p>Declaran estar de acuerdo con la celebración del presente convenio y que es su voluntad la que ha sido libremente expresada y que su consentimiento no se encuentra viciado por dolo, error, mala fe o cualquier otro vicio de la voluntad, que están en completo entendimiento de los efectos y alcance del presente convenio y de que toda la información incluida y adjunta en este convenio es correcta y completa, estampando su firma al margen y al calce para todos los efectos legales que en derecho hubiere lugar</p>

<h2>DEFINICIONES</h2>

<p>INFORMACIÓN CONFIDENCIAL.- Se entenderá por información confidencial toda información ya sea oral, impresa, contenida en medios electrónicos o electromagnéticos, propiedad de las partes, empresas afiliadas o subsidiarias así como por los empleados, agentes o prestadores de servicio de cualquiera de dichas unidades o subsidiarias, obtenida por las partes de terceros, mediante contratos o convenios de licenciamiento, transferencia o servicios, y que se refiera enunciativa pero no limitativamente a: las ideas, fórmulas, normas, manuales, modelos de utilidad, sistemas, procedimientos, informes de clientes, proveedores y/o prestadores de servicios, reportes técnicos, minutas, presupuestos, diseños, dibujos industriales, invenciones, descubrimientos, conceptos, procesos, fórmulas, conocimientos (know-how), mejoras, información, instalaciones, equipo, materiales, métodos, técnicas, resultados de pruebas, técnicas de inspección de calidad, información estadística del proceso, reportes, certificados, especificaciones, manuales de operación, de equipo, datos personales y/o financieros, invenciones, algoritmos, técnicas, información confidencial de terceros, secretos industriales, solicitudes de patentes, patentes, software, derechos de autor, información técnica, industrial, financiera y comercial relativa a nombre de clientes o socios potenciales, estados financieros, balances y declaraciones patrimoniales, propiedades de bienes inmuebles o muebles, domicilios, propuestas de negocios, estructura organizacional y corporativa de la sociedad, planes, proyecciones de mercado, estados de resultados, inventarios, desarrollo de productos, patrones, técnicas, procesos de análisis, marcas registradas, nombres comerciales, documentos de trabajo, compilaciones, procesos de ingeniería de reversa, comparaciones, y cualquiera otro documento al que le den el carácter de confidencial y privilegiada.</p>

<p>PARTE “LA EMPRESA”. Tendrá tal carácter la parte designante que ponga a disposición de la otra parte información confidencial y/o privilegiada que, al hacerse del conocimiento de un tercero, sin su previo consentimiento directa o indirectamente, le ocasione un daño o perjuicio económico, difamación, menoscabo en la imagen, uso de la información para beneficio propio, de la competencia o a terceros, publicidad en contra, competencia desleal, y demás actos que sean en menoscabo de la empresa.</p>

<p>PARTE “EL RECEPTOR”. Tendrá tal carácter la parte que reciba a su resguardo información confidencial propiedad de la parte “LA EMPRESA” o de un tercero, directa o indirectamente, la cual se obliga a no hacerla del conocimiento de un tercero ajeno, bajo pena de indemnizar a la parte “LA EMPRESA” en los términos y condiciones que en este contrato se establecen.</p>

<p>CONTRATO- Contrato origen de la relación que se establezca en este, pudiendo ser laboral, de prestación de servicios, para proveedores, clientes, y demás que considere la empresa objeto de un contrato.</p>

<p>Con base en las declaraciones y definiciones anteriores, se someten al tenor de las siguientes:</p>

<h2>CLÁUSULAS</h2>

<p>PRIMERA. OBJETO. - Bajo las condiciones mencionadas en el contrato laboral, las partes intercambiarán información confidencial, sin que ninguna de ellas tome ventaja de la otra al recibir datos o procedimientos que sean clasificados como confidenciales en los términos de este contrato, limitando su divulgación a aquellas personas físicas o morales que expresamente autorice por escrito “LA EMPRESA”.</p>

<p>Por lo que el trabajador se obliga a:</p>

<p>I. Considerar y mantener en confidencialidad todos los datos, especificaciones, información confidencial y secretos industriales que sea proporcionada por la parte “LA EMPRESA”.</p>

<p>II. No divulgar, publicar o usar en beneficio propio o de terceros dicha información, sin el consentimiento expreso y por escrito de “LA EMPRESA”.</p>

<p>III. Abstenerme de obtener información de carácter confidencial por medio de actos ilícitos o por cualquier otro medio no autorizado por “LA EMPRESA”</p>

<p>IV. Ceder lisa y llanamente a “LA EMPRESA”, los derechos y la facultad de obtener a su favor la patente o registro sobre resultados que se obtengan o puedan obtenerse debido al desarrollo e implementación de cualquier servicio profesional que el suscrito hubiese prestado.</p>

<p>V. Al pago de daños y perjuicios que cause a “LA EMPRESA”, al incumplir cualquiera obligación pactada mediante el presente escrito u otro diverso.</p>

<p>SEGUNDA. DE LA PROPIEDAD DE LA INFORMACIÓN. - La información que se intercambie con motivo de los servicios prestados entrará en el ámbito de dominio de la parte “EL RECEPTOR”. En ningún caso y por ningún motivo se entenderá que la información confidencial compartida por alguna de las partes constituirá una copropiedad.</p>

<p>De la misma forma, el intercambio que se haga de marcas registradas, patentes, derechos de autor, nombres comerciales o cualquier otro derecho amparado por la Ley Federal del Derecho de Autor vigente en el país, leyes similares o sus reformas y adiciones, otorga y expresa tácitamente, el derecho a la explotación comercial de dicha propiedad intelectual, para fines diferentes a aquéllos para los cuales se hayan intercambiado.</p>

<p>SEGUNDA BIS. CLÁUSULA DE EXCLUSIVIDAD. OBJETO. “EL RECEPTOR” acepta el pacto de exclusividad, por virtud de la celebración del presente CONTRATO, y del contrato origen del mismo, respecto a la prestación de sus servicios para “LA EMPRESA”. Esto sin contravenir el artículo quinto de nuestra Carta Magna, pues se prepondera el derecho de voluntad al celebrar el presente. La presente cláusula debe entenderse como una disposición libre, informada y consciente de la libertad de profesión de quien la suscribe, quien se asume que tiene la capacidad para gestionar su propio interés y para beneficiarse en el intercambio contractual de prestaciones debidas.</p>

<p>SEGUNDA BIS 1. APLICACIÓN Y OBLIGACIONES. Los siguientes servicios serán considerados dentro de éste: (I) Asesoría. Bienes y servicios de “LA EMPRESA”, con la finalidad de colocarlo en el mercado y la manera más adecuada para enajenarlo, tomando en consideración sus características y consecuencias fiscales, para el pago de impuestos se efectué en la forma que más le beneficié conforme a las leyes vigentes. Asimismo, la orientación al “CLIENTE” en relación con los Contratos y documentación necesaria para el manejo de la operación y procedimiento con “LA EMPRESA”. (II) Promoción. De los bienes y servicios de “LA EMPRESA” utilizando los medios de difusión que considere posibles compradores que la soliciten, respetando los lineamentos que “LA EMPRESA” tenga vigentes. (III) Mandato. - el mandato mercantil que “LA EMPRESA” le confiera, de acuerdo con las actividades de la misma. (IV) Mediación. Realización de labores de intermediación a fin de lograr la celebración del Contrato adecuado para formalizar la operación de compraventa a favor de “LA EMPRESA”. (V) Gestión. no recibir ningún depósito de dinero, las entregas de dinero de cualquier concepto serán directamente a “LA EMPRESA”. (VI). Información. Rendirá cada mes a “LA EMPRESA” un informe de los resultados de las labores que haya realizado, (VII) Documentación. Se obliga a entregar toda la documentación necesaria a “LA EMPRESA” para proceder con los Contratos de los promitentes compradores. Asimismo, se compromete a entregar dichos documentos digitales y en físico que le sean solicitados por “LA EMPRESA”. (VIII) Acompañamiento, hasta la conclusión de la compraventa y los tramites relativos a este. (IX) Registro. Mantener un registro vigente ante las autoridades fiscales y de seguridad social y pagar todas las contribuciones pertinentes; y (X) Capacitación. Deberá haber completado previamente la capacitación y deberá recibir una capacitación anual, de manera continua, durante su colaboración en la prestación de los Servicios. (XI) Disposiciones. Cumplir en todas y cada una de sus partes las disposiciones contenidas en los Manuales de Procedimientos, Políticas Empresariales y demás disposiciones establecidas, mismas que en este acto manifiesta conocer, prestando sus servicios en los términos establecidos en los mismos, bajo la dirección de “LA EMPRESA” o sus representantes.(XII) otorga y asegura a “LA EMPRESA” la plena y absoluta exclusividad en la prestación de sus servicios en México y en el extranjero de manera directa e indirectamente con un tercero, sin previa autorización por escrito de “LA EMPRESA”. (XIII) comunicar por escrito a “LA EMPRESA” todas y cada una de las ofertas y solicitudes, ya sean por escrito o verbales, que reciba de terceros interesados para su posible contratación para el desempeño de los servicios dentro de los 3 (tres) días hábiles siguientes a la fecha en que dicha oferta o solicitud haya sido hecha.</p>

<p>SEGUNDA BIS 2. VIGENCIA. Esta cláusula tiene vigencia por un año, respetando el derecho humano de trabajar y ejercer su profesión de la manera que desee, sin que se le la presente cláusula contravenga dichos derechos, con la finalidad de establecer la exclusividad de ofrecer los servicios establecidos en el contrato origen, ya que estará usando los medios de “LA EMPRESA”. El contratante podrá solicitar la terminación de esta cláusula con previo aviso de 5 días naturales a partir de la firma del presente y con la autorización de “LA EMPRESA”, podrá ser liberado de la presente. De lo contrario, el incumplimiento de esta cláusula será exigido por la vía judicial.</p>

<p>SEGUNDA BIS 3. CONTRAPRESTACIÓN. Derivado de la celebración del presente CONTRATO y el contrato de origen, EL CONTRATADO será acreedor a una contraprestación, la cual estará establecida en el mencionado contrato.</p>

<p>SEGUNDA BIS 4. SANCIONES. El incumplimiento ocasionado por “EL RECEPTOR” de los acuerdos de exclusividad aquí pactados, dará por terminado el contrato de origen sin responsabilidad alguna a “LA EMPRESA”, y asimismo “EL RECEPTOR” se obliga a pagar en favor de “LA EMPRESA”, los daños y perjuicios que llegaran a generarse de tal incumplimiento, causando una pena convencional y se calculara de acuerdo a la totalidad de las contraprestaciones pactadas en la vigencia establecida, a partir de la firma del presente CONTRATO, conforme a la Unidad de Medida y Actualización vigentes a dicho incumplimiento. Y dejará de percibir todas las contraprestaciones de las que era acreedor. Será considerado como una práctica desleal el incumplimiento de la presente cláusula, y será sancionado conforme al Código Civil y los daños causados por dicho incumplimiento. No obstante, lo dispuesto en los párrafos anteriores, en el caso que un tercero, sea persona física o moral, llegare a reclamar a “LA EMPRESA” ser el acreedor de derechos derivados de la prestación de los servicios del CONTRATADO, este mismo se obliga a absorber y sacar en paz y a salvo a “LA EMPRESA” de cualquier demanda, acción y/o reclamación que pudiera derivarse de dicha reclamación, siempre y cuando sea acreditado ese derecho por autoridad u órgano jurisdiccional.</p>

<p>TERCERA. PERSONAL AUTORIZADO. - Se comprometen a no hacer del conocimiento general la información confidencial que se les haga llegar con motivo de esta prestación de servicios, pudiendo autorizar el conocimiento de tal información sólo al personal que lo requiera de una forma justificada y autorizada por escrito de “LA EMPRESA”.</p>

<p>CUARTA. DE LAS COPIAS Y DE LA DEVOLUCIÓN DE LA INFORMACIÓN A LA PARTE “LA EMPRESA”. – “EL RECEPTOR” no podrá durante y después de la vigencia del presente contrato, en ninguna circunstancia, hacer más ejemplares o copias de la información que las autorizadas por escrito por la parte “LA EMPRESA”.</p>

<p>“LA EMPRESA” tiene en todo tiempo el derecho a solicitar la devolución o destrucción de la información de la que se haya hecho partícipe “EL RECEPTOR”, independientemente del tiempo en que se le hubiere hecho llegar la información confidencial.</p>

<p>QUINTA. EXCEPCIONES A LA CONFIDENCIALIDAD. – “EL RECEPTOR” estará exento de guardar confidencialidad acerca de la información intercambiada, cuando:</p>

<p>Previo a su divulgación, y contando con las pruebas de que la información era conocida por “EL RECEPTOR” libre de toda obligación que la forzara a mantenerla con el carácter de confidencial, según se evidencie por documentación en su posesión;</p>

<p>Es desarrollada o elaborada de manera independiente por las partes “EL RECEPTOR” o legalmente recibida libre de restricciones de otra fuente con derecho a divulgarla;</p>

<p>Es o llega a ser del dominio público, sin mediar incumplimiento de este CONTRATO por la parte “EL RECEPTOR”;</p>

<p>En caso de que alguna autoridad solicite de la parte “EL RECEPTOR” la información confidencial que esté a su resguardo, ésta deberá dar aviso inmediato a la parte “LA EMPRESA”, para que la última tome las medidas que considere pertinentes. En el mismo supuesto, la parte “EL RECEPTOR”, se obliga a proporcionar únicamente la información requerida, y en caso de que los alcances de la investigación no hayan sido delimitados por la autoridad investigadora, se pedirá a la autoridad que la delimite buscando se cause el menor daño posible a la parte de cuya propiedad sea la información sujeta a investigación.</p>

<p>SEXTA. SANCIONES. - En caso de que la parte “EL RECEPTOR” incumpliera con alguna de las obligaciones contenidas en el presente CONTRATO, la parte “LA RECEPTOR” tendrá en términos de ley, la facultad de ejercitar las acciones civiles, penales y administrativas que se deriven de la conducta ilícita de la parte “EL RECEPTOR”, de sus socios, empleados y demás personas a quienes ponga en conocimiento de la información confidencial, de conformidad con la Ley de la Propiedad Industrial, el Código Penal Federal (delito de revelación de secretos contemplado en los artículos 210, 211 y 211 bis) y demás leyes y disposiciones aplicables.</p>

<p>En caso de incumplimiento de la parte “EL RECEPTOR”, independientemente de lo especificado en el punto anterior, se obliga a pagar a la parte “LA EMPRESA” los daños y perjuicios que le sean ocasionados con motivo de su incumplimiento</p>

<p>SÉPTIMA. CESIÓN DE DERECHOS. - Los derechos y obligaciones derivados de este CONTRATO no podrán cederse a un tercero.</p>

<p>OCTAVA. COMUNICACIONES. - Todas las comunicaciones entre las partes deberán ser por escrito, con acuse de recibo o por cualquier otro medio que garantice que el destinatario recibió la comunicación, a los domicilios estipulados en las declaraciones.</p>

<p>En caso de que cualquiera variara su domicilio, deberá notificarlo a la otra parte con, al menos, quince días hábiles de anticipación a la fecha en que ocurra tal evento, de lo contrario se entenderá que las comunicaciones que conforme a este CONTRATO deban darse, surtirán efectos legales en el último domicilio del que se tenga conocimiento.</p>

<p>NOVENA. SALVAGUARDA LABORAL. - Ambas partes reconocen que el presente CONTRATO no podrá interpretarse como constitutivo de una relación laboral, de asociación, sociedad, licencia o de cualquier otra índole.</p>

<p>DÉCIMA. NO OTORGAMIENTO DE DERECHOS. - La parte “EL RECEPTOR” reconoce que el hecho de que la parte “LA EMPRESA” le comparta información confidencial no le otorga ningún derecho de licencia, patente o propiedad intelectual sobre la misma y que la revelación de la información no originará ninguna obligación a la parte “LA EMPRESA” de otorgar derecho alguno sobre dicha información.</p>

<p>DÉCIMA PRIMERA. VIGENCIA. - Las partes acuerdan en que las obligaciones derivadas del presente CONTRATO permanecerán en vigor durante la duración del CONTRATO y por un periodo de 5 (cinco) años a partir de la terminación del contrato, aun cuando no se haya desarrollado ninguna negociación conjunta; o bien, mientras la información mencionada no se haga pública o pierda el carácter de confidencial y privilegiada.</p>

<p>DÉCIMA SEGUNDA. POSTERIOR AL TERMINO DE CONTRATO. - Queda prohibido laborar de una manera directa o indirecta con cualquier empresa considerada como competencia de “LA EMPRESA” o cualquiera de sus filiales y/o crear una empresa con el mismo giro comercial al de “LA EMPRESA” 5 (cinco) años posterior al término del contrato.</p>

<p>DÉCIMA TERCERA. CONTROVERSIAS. - En caso de presentarse controversia en la aplicación, interpretación o cumplimiento de las obligaciones derivadas del presente CONTRATO, las partes designarán un árbitro para que decida en derecho sobre la controversia surgida.</p>

<p>De no llegar a algún acuerdo, se someterán a la jurisdicción de los tribunales competentes de {{ $empresa->entidad_jurisdiccion }}, renunciando expresamente a cualquier otro que les correspondiera ya sea en razón de la materia, domicilio o cuantía, en el presente o en el futuro.</p>

<div class="closure">
<p>Se firma el presente CONTRATO por duplicado en {{ $empresa->ciudad_firma }}, {{ $fechaCelebracionTexto }}.</p>

@include('pdf.contrato-firmas')
</div>
</body>
</html>
