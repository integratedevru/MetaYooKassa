# Meta ЮKassa WordPress плагин

## Описание
Этот плагин для WordPress реализует шлюз платежей через ЮKassa и позволяет скачивать данные, связанные с успешными платежами.

## Автор
MetaSystems (для ПКС)

## Установка
1. Поместите файлы репозитория в папку `meta-yookassa` в директории `/wp-content/plugins/` установленного WordPress.
2. Активируйте плагин в меню 'Плагины' административной панели WordPress.
3. Перейдите в настройки 'Meta ЮKassa' в административной панели WordPress для настройки параметров плагина.

## Использование
Используйте шорткод `[custom_form]` для отображения формы оплаты на вашем сайте WordPress. Пользователи могут указать свое полное имя, выбрать район Псковской области, ввести номер счета за коммунальные услуги и указать сумму платежа.

## Настройка
Настройте параметры плагина в разделе 'Meta ЮKassa' в меню администратора WordPress. Укажите идентификатор магазина ЮKassa и секретный ключ. Вы также можете включить тестовый режим для проверки работы плагина.

## Скачивание реестра
Администраторы могут скачать CSV-файл с данными об успешных платежах. Чтобы скачать данные о платежах, перейдите на страницу настроек 'Meta YooKassa' и нажмите кнопку 'Скачать реестр успешных платежей (.csv)'.

## Интеграция с API ЮKassa
Этот плагин интегрируется с API ЮKassa для обработки платежей. Убедитесь, что ваши учетные данные ЮKassa правильно указаны в настройках плагина.