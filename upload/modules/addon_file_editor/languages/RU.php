<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the English text outputs of the module.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version     $Id$
 * @language	Russian
 * @translation	konstantinmsk
 * @platform	Website Baker 2.8
*/

// German module description
$module_description = 'AFE позволяет редактировать изображения и текстовые файлы установленных добавлений через панель администрирования. Ознакомьтесь с <a href="{WB_URL}/modules/addon_file_editor/help/help_en.html" target="_blank">README</a> (англ.) для дополнительноый информации).';

// declare module language array
$LANG = array();

// Text outputs for the version check
$LANG[0] = array(
	'TXT_VERSION_ERROR'			=> 'Ошибка: модуль "Addon File Editor" работает только с версией CMS WebsiteBaker 2.7. и выше, а также Lepton.',
);

// Text outputs overview page (htt/addons_overview.htt)
$LANG[1] = array(
	'TXT_DESCRIPTION'			=> 'Этот список показывает все читаемые PHP модули. Вы можете редактировать добавления' . 
								   'просто кликнув на названии. Иконка download/скачать позволяет создать резервную копию с возможностью ' .
								   'последующей инсталляции на лету.',
	'TXT_FTP_NOTICE'			=> 'Выделенные добавления/файлы недоступны для записи. Это может быть вызвано тем,' .
								   'что установка осуществлялась по FTP. Вам нужно <a class="link" target="_blank" href="{URL_ASSISTANT}">' .
								   'включить поддержку FTP </a> для редактирования этих файлов.',
	'TXT_HEADING'				=> 'Установленные добавления (Модули, Шаблоны, Языковые файлы)',
	'TXT_HELP'					=> 'Помощь',

	'TXT_RELOAD'				=> 'Обновить',
	'TXT_ACTION_EDIT'			=> 'Редактирование',
	'TXT_ACTION_DELETE'			=> 'Удаление',
	'TXT_FTP_SUPPORT'			=> '(для изменения требуются права для записи по FTP)',

	'TXT_MODULES'				=> 'Модуль',
	'TXT_LIST_OF_MODULES'		=> 'Перечень установленных модулей',
	'TXT_EDIT_MODULE_FILES'		=> 'Редактирование файлов модуля',
	'TXT_ZIP_MODULE_FILES'		=> 'Резервное копирование и скачивание файлов модуля',

	'TXT_TEMPLATES'				=> 'Шаблоны',
	'TXT_LIST_OF_TEMPLATES'		=> 'Перечень установленных шаблонов',
	'TXT_EDIT_TEMPLATE_FILES'	=> 'Редактирование файлов шаблона',
	'TXT_ZIP_TEMPLATE_FILES'	=> 'Резервное копирование и скачивание файлов шаблона',

	'TXT_LANGUAGES'				=> 'Языковые файлы',
	'TXT_LIST_OF_LANGUAGES'		=> 'Перечень установленных языковых файлов',
	'TXT_EDIT_LANGUAGE_FILES'	=> 'Редактирование языкового файла',
	'TXT_ZIP_LANGUAGE_FILES'	=> 'Скачивание языкового файла',
);

// Text outputs filemanager page (htt/filemanager.htt)
$LANG[2] = array(
	'TXT_EDIT_DESCRIPTION'		=> 'Файл-менеджер позволяет осуществлять любые операции с файлами. Нажатие на название ' .
								   'текстового файла или изображения открывает файл для его изменения или просмотра.',
	'TXT_BACK_TO_OVERVIEW'		=> 'Назад к описанию добавления',

	'TXT_MODULE'				=> 'Модуль',
	'TXT_TEMPLATE'				=> 'Шаблон',
	'TXT_LANGUAGE'				=> 'Языковой файл',
	'TXT_FTP_SUPPORT'			=> '(для изменения требуются права для записи по FTP)',

	'TXT_RELOAD'				=> 'Обновить',
	'TXT_CREATE_FILE_FOLDER'	=> 'Создание файла/папки',
	'TXT_UPLOAD_FILE'			=> 'Загрузить файл',
	'TXT_VIEW'					=> 'Просмотр',
	'TXT_EDIT'					=> 'Редактирование',
	'TXT_RENAME'				=> 'Переименование',
	'TXT_DELETE'				=> 'Удаление',

	'TXT_FILE_INFOS'			=> 'Информация о файле',
	'TXT_FILE_ACTIONS'			=> 'Действия',
	'TXT_FILE_TYPE_TEXTFILE'	=> 'Текстовый файл',
	'TXT_FILE_TYPE_FOLDER'		=> 'Папка',
	'TXT_FILE_TYPE_IMAGE'		=> 'Файл изображения',
	'TXT_FILE_TYPE_ARCHIVE'		=> 'Файл архива',
	'TXT_FILE_TYPE_OTHER'		=> 'Неизвестный',

	'DATE_FORMAT'				=> 'м/д/г / ч:м',
);

// General text outputs for the file handler templates
$LANG[3] = array(
	'ERR_WRONG_PARAMETER'		=> 'Указанные параметры неверны или недостаточно полны.',
	'TXT_MODULE'				=> 'Модуль',
	'TXT_TEMPLATE'				=> 'Шаблон',
	'TXT_LANGUAGE'				=> 'Языковой файл',
	'TXT_ACTION'				=> 'Действие',
	'TXT_ACTUAL_FILE'			=> 'Текущий файл',
	'TXT_SUBMIT_CANCEL'			=> 'Отмена',
);	

// Text outputs file handler (htt/action_handler_edit_textfile.htt)
$LANG[4] = array(
	'TXT_ACTION_EDIT_TEXTFILE'	=> 'Редактирование текстовго файла',
	'TXT_SUBMIT_SAVE'			=> 'Сохранить',
	'TXT_SUBMIT_SAVE_BACK'		=> 'Сохранить &amp; назад',
	'TXT_ACTUAL_FILE'			=> 'Текущий файл',
	'TXT_SAVE_SUCCESS'			=> 'Изменения файла успешно сохранены.',
	'TXT_SAVE_ERROR'			=> 'Невозможно сохранить файл. Проверьте разрешения (permissions).',
);

// Text outputs file handler (htt/action_handler_rename_file_folder.htt)
$LANG[5] = array(
	'TXT_ACTION_RENAME_FILE'	=> 'Переименование файла/папки',
	'TXT_OLD_FILE_NAME'			=> 'Название файла/папки (старое)',
	'TXT_NEW_FILE_NAME'			=> 'Название файла/папки (новое)',
	'TXT_SUBMIT_RENAME'			=> 'Переименование',
	'TXT_RENAME_SUCCESS'		=> 'Переименование файла/папки прошло успешно.',
	'TXT_RENAME_ERROR'			=> 'Переименование файла/папки невозможно. Проверьте разрешения (permissions).',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
);

// Text outputs file handler (htt/action_handler_delete_file_folder.htt)
$LANG[6] = array(
	'TXT_ACTION_DELETE_FILE'	=> 'Удаление файла/папки',
	'TXT_SUBMIT_DELETE'			=> 'Удаление',
	'TXT_ACTUAL_FOLDER'			=> 'Текущая папка',
	'TXT_DELETE_WARNING'		=> '<strong>Внимание: </strong>Отмена удаления невозможна! Помните, что' .
								   'при удалении папки удаляется всё её содержимое.',
	'TXT_DELETE_SUCCESS'		=> 'Удаление файла/папки прошло успешно.',
	'TXT_DELETE_ERROR'			=> 'Удаление файла/папки невозможно. Проверьте разрешения (permissions).<br /><em>Внимание: для удаления папки ' .
								   'по FTP, убедитесь, что папка пуста.</em>'
);

// Text outputs file handler (htt/action_handler_create_file_folder.htt)
$LANG[7] = array(
	'TXT_ACTION_CREATE_FILE'	=> 'Создание файла/папки',
	'TXT_CREATE'				=> 'Создание',
	'TXT_FILE'					=> 'файла',
	'TXT_FOLDER'				=> 'папки',
	'TXT_FILE_NAME'				=> 'Название',
	'TXT_ALLOWED_FILE_CHARS'	=> '[a-zA-Z0-9.-_]',
	'TXT_TARGET_FOLDER'			=> 'Конечная папка',
	'TXT_SUBMIT_CREATE'			=> 'Создание',
	'TXT_CREATE_SUCCESS'		=> 'Создание файла/папки успешно завершено.',
	'TXT_CREATE_ERROR'			=> 'Создание файла/папки невозможно. Проверьте разрешения (permissions) и допустимость названия.',
);

// Text outputs file handler (htt/action_handler_upload_file.htt)
$LANG[8] = array(
	'TXT_ACTION_UPLOAD_FILE'	=> 'Загрузка файла',
	'TXT_SUBMIT_UPLOAD'			=> 'Загрузка',

	'TXT_FILE'					=> 'Файл',
	'TXT_TARGET_FOLDER'			=> 'Конечная папка',

	'TXT_UPLOAD_SUCCESS'		=> 'Загрузка файла успешно завершена.',
	'TXT_UPLOAD_ERROR'			=> 'Загрузка файла невозможна.  Проверьте разрешения (permissions) и размер файла.',
);

// Text outputs for the download handler
$LANG[9] = array(
	'ERR_TEMP_PERMISSION'		=> 'PHP не имеет разрешения на запись в папку для временных файлов(/temp).',
	'ERR_ZIP_CREATION'			=> 'Невозможно создать архив.',
	'ERR_ZIP_DOWNLOAD'			=> 'Ошибка при скачивании файла резервной копии.<br /><a href="{URL}">Скачать</a> вручную.',
);

// Text outputs for the FTP checking (htt/ftp_connection_check.htt)
$LANG[10] = array(
	'TXT_FTP_HEADING'			=> 'Асситент конфигурирования FTP (FTP assistant)',
	'TXT_FTP_DESCRIPTION'		=> ' FTP assistant позволяет настроить и протестировать FTP для AFE.',

	'TXT_FTP_SETTINGS'			=> 'Актуальные настройки FTP',
	'TXT_FTP_SUPPORT'			=> 'Поддержка FTP',
	'TXT_ENABLE'				=> 'Вкл.',
	'TXT_DISABLE'				=> 'Выкл.',
	'TXT_FTP_SERVER'			=> 'Сервер',
	'TXT_FTP_USER'				=> 'Пользователь',
	'TXT_FTP_PASSWORD'			=> 'Пароль',
	'TXT_FTP_PORT'				=> 'Порт',
	'TXT_FTP_START_DIR'			=> 'Начальная директория',

	'TXT_FTP_CONNECTION_TEST'	=> 'Проверьте FTP соединение',
	'TXT_CHECK_FTP_CONNECTION'	=> 'Нажмите кнопку ниже для проверки состояния подключения к  FTP серверу.',
	'TXT_FTP_CHECK_NOTE'		=> '<strong>Внимание: </strong>Тест соединения может занимать до 30 секунд.',
	'TXT_SUBMIT_CHECK'			=> 'Соединение.',
	'TXT_FTP_LOGIN_OK'			=> 'Соединение с FTP сервером успешно установлено. Поддержка FTP включена.',
	'TXT_FTP_LOGIN_FAILED'		=> 'Соединение с FTP невозможно. Проверьте настройки. ' .
								   '<br /><strong>Помните:</strong> параметр "начальная директория" должен указывать на папку, <br />где установлена CMS Websitebaker или Lepton.',
);

?>