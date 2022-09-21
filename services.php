<?php
/*
Template Name: Шаблон услуг2
 */

get_header(); ?>
<div class="service-breadcrumb">
    <a href="/"> Главная </a> &nbsp;&#47;&nbsp; <a href="<?php echo get_page_link(955);?>"> Индивидуальные услуги </a> &nbsp;&#47;&nbsp; <?php the_title() ?>
</div>
<?php
 $utp = get_post_meta($post->ID, 'utp', true);
?>
<main class="main default-page">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="row">
                <h1><?php echo the_title(); ?></h1>
            <div class="large-24 columns">
                <?php the_content(); ?>
                <?php
                    $price = get_post_meta($post->ID, 'price', true); 
                    if ($price) {
                        echo $price;
                    }
                ?>
                <h2>Запись на онлайн консультацию</h2>
                <p><strong>Для записи на консультацию:</strong></p>
                <ul>
                    <li>Звоните по телефону +7(812)611-18-00</li>
                    <li>Пишите по электронной почте <a href="mailto:govorysha5@gmail.com">govorysha5@gmail.com</a></li>
                    <li>Укажите ваши данные в форме записи — и мы вам перезвоним:</li>
                </ul>
                <?php echo do_shortcode( '[contact-form-7 id="2860" title="Форма записи на консультацию"]' ); ?>
                <?php
                    $teachers = get_post_meta($post->ID, 'teachers', true); 
                    if ($teachers) {
                        echo $teachers;
                    }
                
                $args = array(
                    'number'  => 3,
                    'orderby' => 'comment_date',
                    'order'   => 'DESC',
                    'status'  => 'approve',
                    'type'    => 'comment', // только комментарии, без пингов и т.д...
                );

                if( $comments = get_comments( $args ) ){
                    echo '<span class="service-reviews">Отзывы</span>';
                    echo '<ul class="service-review">';
                    foreach( $comments as $comment ){
                        echo '<li> Автор: <span>'. $comment->comment_author . '</span><br> ' . $comment->comment_content . '</li>';
                    }
                    echo '</ul>';
                    echo '<a href="/otzyvy/">Все отзывы</a>';
                } 
                ?>
            </div>
        </div>

    <?php endwhile;
    endif;?>
</main>

<?php get_footer(); ?>