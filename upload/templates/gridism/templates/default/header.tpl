<?php
    $phpparser = CAT_Object::parser();
    $base64    = CAT_Users::get_avatar();
    $avatar    = $base64 ? 'url(\''.$base64.'\')' : null;
?>
<!DOCTYPE html>
<html lang="<?= $data['META']['LANGUAGE'] ?>">
<head>
	{get_page_headers( "backend" , true , "$section_name")}
</head>
<body>
    <div class="grid-container">
        <header class="header">
            <i class="icon-dots"></i>
        	<nav>
				<?php foreach($data['MAIN_MENU'] as $menu): ?>
				<?php     if($menu['permission'] == true): ?>
					<a href="<?= $menu['link'] ?>" class="icon-<?= $menu['permission_title'] ?><?php if($menu['current'] == true): ?> current<?php endif; ?>" title="<?= $menu['title'] ?>"></a>
                <?php     endif; ?>
				<?php endforeach; ?>
    		</nav>
            <div class="header__avatar"<?php if($avatar): ?> style="background-image:<?= $avatar ?>"<?php endif; ?>>
                <div class="dropdown">
                    <ul class="dropdown__list">
                        <li class="dropdown__list-item">
                            <span class="dropdown__icon"><i class="icon icon-preferences"></i></span>
                            <span class="dropdown__title"><a href="{$PREFERENCES.link}" title="{$PREFERENCES.title}"><?= CAT_Object::lang()->translate('Preferences') ?></a></span>
                        </li>
                        <li class="dropdown__list-item">
                            <span class="dropdown__icon"><i class="icon icon-logout"></i></span>
                            <span class="dropdown__title"><a href="{$CAT_ADMIN_URL}/logout/" title="<?= CAT_Object::lang()->translate('Logout') ?>"><?= CAT_Object::lang()->translate('Logout') ?></a></span>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <aside class="sidenav">
            <div class="sidenav__brand">
                <i class="icon icon-brand"></i>
                <a class="sidenav__brand-link" href="#">BlackCat CMS</a>
                <i class="fas fa-times sidenav__brand-close"></i>
            </div>
            <div class="sidenav__profile">
                <div class="sidenav__profile-avatar"<?php if($avatar): ?> style="background-image:<?= $avatar ?>"<?php endif; ?>></div>
                <div class="sidenav__profile-title"><?= CAT_Users::get_username() ?></div>
            </div>
            <?php if(!empty($data['pages_tree'])): ?>
            <nav class="sidenav__tree-nav">
                <?= $data['pages_ul'] ?>
            </nav>
            <?php endif; ?>
        </aside>
        <main class="main">
            <div class="main-header">
                <div class="main-header__intro-wrapper">
                    <div class="main-header__welcome">
                        <div class="main-header__welcome-title text-light"><?= CAT_Object::lang()->translate('Welcome') ?>, <strong><?= CAT_Users::get_display_name() ?></strong></div>
                        <div class="main-header__welcome-subtitle text-light">How are you today?</div>
                    </div>
                </div>
            </div>
            <div class="main-content">