<?php
/**
 * GeoDirectory Events dummy data for standard events.
 *
 * @since 2.0.0
 * @package GeoDirectory_Event_Manager
 */

global $dummy_image_url, $dummy_categories, $dummy_custom_fields, $dummy_posts, $dummy_sort_fields;

$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/';

// Dummy categories
$dummy_categories  = array();
$dummy_categories['food-drink'] = array(
	'name'        => 'Food & Drink',
	'icon'        => $dummy_image_url . 'cat_icon/food-drink.svg',
	'schema_type' => 'FoodEvent',
	'font_icon'   => 'fas fa-glass-martini',
	'color'       => '#803fc7',
);
$dummy_categories['festivals'] = array(
	'name'        => 'Festivals',
    'icon'        => $dummy_image_url . 'cat_icon/festivals.svg',
	'schema_type' => 'Event',
	'font_icon'   => 'fas fa-ticket-alt',
	'color'       => '#20abce',
);
$dummy_categories['music'] = array(
    'name'        => 'Music',
    'icon'        => $dummy_image_url . 'cat_icon/music.svg',
    'schema_type' => 'Event',
    'font_icon'   => 'fas fa-music',
    'color'       => '#dc19e3',
);
$dummy_categories['sport'] = array(
    'name'        => 'Sport',
    'icon'        => $dummy_image_url . 'cat_icon/sport.svg',
    'schema_type' => 'SportsEvent',
    'font_icon'   => 'fas fa-futbol',
    'color'       => '#108f2d',
);
$dummy_categories['business'] = array(
    'name'        => 'Business',
    'icon'        => $dummy_image_url . 'cat_icon/business.svg',
    'schema_type' => 'BusinessEvent',
    'font_icon'   => 'fas fa-briefcase',
    'color'       => '#077c94',
);
$dummy_categories['health-beauty'] = array(
    'name'        => 'Health & Beauty',
    'icon'        => $dummy_image_url . 'cat_icon/health-beauty.svg',
    'schema_type' => 'HealthAndBeautyEvent',
    'font_icon'   => 'fas fa-dumbbell',
    'color'       => '#874a04',
);

$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/events/';


// Custom fields
$dummy_custom_fields = GeoDir_Admin_Dummy_Data::extra_custom_fields( $post_type );

// Set any sort fields
$dummy_sort_fields = array();

// date added
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => '',
	'field_type' => 'datetime',
	'frontend_title' => __('Event date','geodirectory'),
	'htmlvar_name' => 'event_dates',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '1',
);

// date added
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => '',
	'field_type' => 'datetime',
	'frontend_title' => __('Newest','geodirectory'),
	'htmlvar_name' => 'post_date',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '0',
);

// title
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'text',
	'frontend_title' => __('Title','geodirectory'),
	'htmlvar_name' => 'post_title',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '0',
);

// rating
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'float',
	'frontend_title' => __('Rating','geodirectory'),
	'htmlvar_name' => 'overall_rating',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '0',
);

// Dummy posts
$dummy_posts = array();

$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Street Italian Market Festival',
	"post_content" 	=> '<h3>The Experience </h3>

For one weekend each May, 9th Street - in the heart of South Philadelphia - closes down traffic and a huge, multi-block festival takes over the neighborhood.

It all starts with the great sights, sounds and aromas of America&acute;s oldest continuously operating open-air market: South Philadelphia&acute;s famous Italian Market. And the most important thing for you to bring with you is your appetite.

In addition to the blocks of curb vendors and specialty butcher, cheese, gift and cookware shops that line the market, there will also be street-side merchants selling specially prepared foods just for the Festival.

Expect to see stands offering a display of fresh sausage and peppers, antipasto salads, roast pork sandwiches, cheeses, cured meats, an infinite array of pastries, famous mango roses and so much more.

Many nearby restaurants will extend their table service to the sidewalk so you can dine alfresco and enjoy the festival atmosphere.

A stunning smorgasbord of flavors will be on full display during the Festival, as vendors line the street, musicians roam the crowds and top chefs show off some of their best techniques at live cooking demonstrations.

For a full schedule and lineup of musicians, performances and demonstrations, be sure to visit the Festival&acute;s official website.
<h3>Insider Tip </h3>

Belying its name, the Italian Market is not just Italian anymore. In fact, it&acute;s a veritable melting pot of international cultures and cuisines.

You can choose from several excellent Asian restaurants serving delicious Vietnamese banh mi sandwiches and piping hot bowls of pho.

Or savor amazingly flavorful tacos, spicy tamales and several other authentic Mexican favorites from La Lupe and Taqueria La Veracruzanas. And that&acute;s just the beginning.

There is so much great eating in and around the Italian Market that you&acute;ll want to return again and again.',
	"post_images" 	=> array(
		$dummy_image_url . "event16.webp",
		$dummy_image_url . "event17.webp",
		$dummy_image_url . "event15.webp",
		$dummy_image_url . "event4.webp",
		$dummy_image_url . "event5.webp",
		$dummy_image_url . "event6.webp",
		$dummy_image_url . "event7.webp",
		$dummy_image_url . "event8.webp",
		$dummy_image_url . "event9.webp",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'italian market', 'italian festival' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@italianmarketfestival.com',
	"website"       => 'http://www.italianmarketfestival.com',
	"twitter"       => 'http://twitter.com/italianmarketfestival',
	"facebook"      => 'http://facebook.com/italianmarketfestival',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+30 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '10:00',
		'end_time' 		=> '12:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Caribbean New',
	"post_content" 	=> '<h3>The Experience</h3>

Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.

With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.

Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.

In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
Additional Information

Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.',
	"post_images" 	=> array(
		$dummy_image_url . "event1.webp",
		$dummy_image_url . "event3.webp",
		$dummy_image_url . "event2.webp",
		$dummy_image_url . "event11.webp",
		$dummy_image_url . "event6.webp",
		$dummy_image_url . "event9.webp",
		$dummy_image_url . "event10.webp",
		$dummy_image_url . "event4.webp",
		$dummy_image_url . "event5.webp",
	),
	"post_category" =>  array( 'Food & Drink' ) ,
	"post_tags"     => array( 'caribbean food' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@pennslandingcorp.com',
	"website"       => 'http://www.pennslandingcorp.com',
	"twitter"       => 'http://twitter.com/pennslandingcorp',
	"facebook"      => 'http://facebook.com/pennslandingcorp',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+7 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '08:00',
		'end_time' 		=> '21:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Festival, Concert and Fireworks',
	"post_content" 	=> 'This Fourth of July, celebrate America independence with incredible fireworks in Philadelphia during the annual Wawa Welcome America! festival!

THE MAIN EVENT

<h3>Concert & Fireworks Display </h3>

8:30 – 11:00 p.m., July 4, 2010

CONCERT BEGINS AT 8:30 – FIREWORKS BEGIN AROUND 10:30

FIREWORKS LOCATION: Philadelphia Museum of Art, Benjamin Franklin Parkway
Where to Watch the Fireworks on the 4th:

There are several great places to watch the fireworks.

- Lemon Hill
– Benjamin Franklin Parkway
– Boathouse Row
– Kelly Drive
– Martin Luther King Drive
– Schuylkill River Park

Time: The fireworks display is estimated to begin around 10:30 p.m

<h3> Where to Watch the Concert: </h3>

The best place from which to watch the concert is on the Benjamin Franklin Parkway. Giant screens and speakers will broadcast the concert all along the Parkway.
<h3>Viewing Tips: </h3>

Arrive early. Bring lawn chairs, a blanket and a picnic. If you get to the Parkway early, you will be able to grab a great location for viewing the concert and the fireworks.

<h3>Concert Details & Performers </h3>

Concert begins at 8:30 p.m., July 4, 2010

The Goo Goo Dolls will headline this year&acute;s concert, which features performances by Philly favorites: The Roots, R&B singer Chrisette Michelle and Washington D.C.&acute;s Chuck Brown.
July 4th Parade in Historic Philadelphia, 11:00 a.m., July 4, 2010

This year, Philadelphia&acute;s main parade fittingly takes place in Historic Philadelphia. Do not miss it!
Party on the Parkway Festival, 1:00 – 7:00 p.m., July 4, 2010

Bring your appetite and your red, white and blue apparel as an exciting, family-friendly festival stretches along Benjamin Franklin Parkway from The Franklin to the steps of the Philadelphia Museum of Art.

<h3>Insider Tip </h3>

Bring lawn chairs, a blanket and a picnic while you watch the parade. Then stay for the concert and fireworks. If you arrive early, you&acute;ll be able to grab a great location for viewing all three.',
	"post_images" 	=> array(
        $dummy_image_url . "event6.webp",
        $dummy_image_url . "event7.webp",
        $dummy_image_url . "event4.webp",
        $dummy_image_url . "event5.webp",
        $dummy_image_url . "event11.webp",
		$dummy_image_url . "music2.webp",
		$dummy_image_url . "music1.webp",
		$dummy_image_url . "music3.webp",
		$dummy_image_url . "music8.webp",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'concert', 'fireworks' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@italianmarketfestival.com',
	"website"       => 'http://www.italianmarketfestival.com',
	"twitter"       => 'http://twitter.com/italianmarketfestival',
	"facebook"      => 'http://facebook.com/italianmarketfestival',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+5 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '11:00',
		'end_time' 		=> '15:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Caribbean Festival',
	"post_content" 	=> '<h3>The Experience</h3>

Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.

With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.

Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.

In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
Additional Information

Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.',
	"post_images" 	=> array(
        $dummy_image_url . "event2.webp",
        $dummy_image_url . "event3.webp",
        $dummy_image_url . "event1.webp",
        $dummy_image_url . "event11.webp",
        $dummy_image_url . "event5.webp",
        $dummy_image_url . "event4.webp",
        $dummy_image_url . "event6.webp",
        $dummy_image_url . "event7.webp",
		$dummy_image_url . "event8.webp",
	),
	"post_category" =>  array( 'Food & Drink', 'Festivals' ) ,
	"post_tags"     => array( 'caribbean' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@pennslandingcorp.com',
	"website"       => 'http://www.pennslandingcorp.com',
	"twitter"       => 'http://twitter.com/pennslandingcorp',
	"facebook"      => 'http://facebook.com/pennslandingcorp',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+5 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '',
		'end_time' 		=> '',
		'all_day' 		=> '1',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Kennett Square Mushroom Festival',
	"post_content" 	=> '<h3>The Experience</h3>

Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.

With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.

Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.

In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
Additional Information

Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.
',
	"post_images" 	=> array(
		$dummy_image_url . "event9.webp",
        $dummy_image_url . "event8.webp",
        $dummy_image_url . "event17.webp",
        $dummy_image_url . "music10.webp",
		$dummy_image_url . "event13.webp",
		$dummy_image_url . "event1.webp",
		$dummy_image_url . "event3.webp",
		$dummy_image_url . "event5.webp",
		$dummy_image_url . "event7.webp",
	),
	"post_category" =>  array( 'Food & Drink' ) ,
	"post_tags"     => array( 'food', 'mushroom' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@italianmarketfestival.com',
	"website"       => 'http://www.italianmarketfestival.com',
	"twitter"       => 'http://twitter.com/italianmarketfestival',
	"facebook"      => 'http://facebook.com/italianmarketfestival',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+5 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '11:00',
		'end_time' 		=> '15:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Reading Terminal Markets Pennsylvania Dutch Festival',
	"post_content" 	=> 'Celebrate the traditions, foods and crafts of the Pennsylvania Dutch at the 21st annual Pennsylvania Dutch Festival at the historic Reading Terminal Market.

The three-day festival will take place in the Market&acute;s center court seating area and will feature handmade crafts including quilts, woodcrafts, paintings, hand braided rugs, wooden toys and cedar chests.

Traditional foods including chicken pot pie, donuts, ice cream, pies and canned fruits and vegetables will be available to taste and purchase.

On Saturday, August 13, the festival moves outdoors to create a country fair in the city. The 1100 block of Arch Street will be closed to traffic and a petting zoo with sheep, goats, chickens, donkeys, calves, horses and pigs will fill the street.

Amish buggy rides and horse drawn wagon rides around the Market, as well as country and bluegrass bands, round out the entertainment for this great, family-friendly event.',
	"post_images" 	=> array(
        $dummy_image_url . "event13.webp",
        $dummy_image_url . "event5.webp",
        $dummy_image_url . "event4.webp",
        $dummy_image_url . "event16.webp",
        $dummy_image_url . "event17.webp",
        $dummy_image_url . "music10.webp",
        $dummy_image_url . "event3.webp",
		$dummy_image_url . "event14.webp",
		$dummy_image_url . "event15.webp",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'dutch festival', 'woodcrafts' ),
	"video"         => '',
	"phone"       	=> '(000) 111-4444',
	"email"         => 'info@readingterminalmarket.com',
	"website"       => 'http://www.readingterminalmarket.com',
	"twitter"       => 'http://twitter.com/readingterminalmarket',
	"facebook"      => 'http://facebook.com/readingterminalmarket',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+10 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '10:30',
		'end_time' 		=> '12:30',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Philadelphia Gay and Lesbian Theatre Festival',
	"post_content" 	=> 'The Philadelphia Gay and Lesbian Theatre Festival has been canceled for 2010.

The Seventh Annual Philadelphia Gay and Lesbian Theatre Festival (PGLTF) begins its loud and proud two-week run on June 11, 2009. Several theater productions celebrate the gay, lesbian, bisexual and transgender experience through the art of theater.

The festival typically included both local and international premieres of critically acclaimed dramas, comedies, musicals and one-person shows. All productions aim to entertain, educate, empower, enlighten, challenge and delight audiences.

Topics of previous productions included a musical review of favorite Broadway tunes coming to life with a decidedly gay perspective; dealing with one inner burdens while on a pilgrimage to India; turning the damages of sexual abuse to that which gives rise to transformation; intertwined lives of gay men and the women who love them; delving into whether Shakespeare was bi-sexual and if the subject of his love sonnets was a young boy; as well as two productions specifically presented as a part of our Young Audience Presentations.',
	"post_images" 	=> array(
        $dummy_image_url . "event12.webp",
        $dummy_image_url . "event10.webp",
        $dummy_image_url . "health_beauty1.webp",
        $dummy_image_url . "event18.webp",
        $dummy_image_url . "event19.webp",
        $dummy_image_url . "event5.webp",
		$dummy_image_url . "event7.webp",
		$dummy_image_url . "event1.webp",
		$dummy_image_url . "event6.webp",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'pgltf', 'theatre' ),
	"video"         => '',
	"phone"       	=> '(000) 111-8888',
	"email"         => 'info@pgltf.com',
	"website"       => 'http://www.pgltf.com',
	"twitter"       => 'http://twitter.com/pgltf',
	"facebook"      => 'http://facebook.com/pgltf',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+10 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '11:30',
		'end_time' 		=> '15:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Super Scooper All-You-Can-Eat Ice Cream Festival',
	"post_content" 	=> '<h3>The Experience</h3>

What better way to raise money for children with leukemia than to eat your favorite kind of ice cream?

At Wawa Welcome America!‘s annual Super Scooper All-You-Can-Eat Ice Cream Festival, you can do just that - as well as enjoy free music, live entertainment and games for the whole family!

At this annual celebration of sweetness, more than 20 ice cream and water ice companies will serve up their cool, creamy treats. After paying the $5 admission, ice cream lovers are given a spoon and unlimited access to their favorites. Clearly, this is no time to count calories.

All proceeds from the event will benefit the Joshua Kahan Fund and the fight to cure pediatric leukemia.
<h3>Additional Information </h3>',
	"post_images" 	=> array(
        $dummy_image_url . "event18.webp",
        $dummy_image_url . "event19.webp",
        $dummy_image_url . "event13.webp",
        $dummy_image_url . "health_beauty1.webp",
		$dummy_image_url . "event11.webp",
		$dummy_image_url . "event10.webp",
		$dummy_image_url . "event1.webp",
		$dummy_image_url . "event5.webp",
		$dummy_image_url . "event6.webp",
	),
	"post_category" =>  array( 'Food & Drink' ) ,
	"post_tags"     => array( 'ice cream', 'music' ),
	"video"         => '',
	"phone"       	=> '(000) 111-5555',
	"email"         => 'info@welcomeamerica.com',
	"website"       => 'http://www.welcomeamerica.com',
	"twitter"       => 'http://twitter.com/welcomeamerica',
	"facebook"      => 'http://facebook.com/welcomeamerica',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+10 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '10:15',
		'end_time' 		=> '12:15',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'The Roots Picnic',
	"post_content" 	=> '<h3>Location </h3>

Festival Pier at Penn&acute;s Landing
Columbus Boulevard at Spring Garden Street
<h3>The Festival </h3>

The Roots - the Philly natives also known as the Legendary Roots Crew - have gathered a diverse lineup of talent for this third annual music festival, including: Vampire Weekend, Mayer Hawthorne, The Very Best, Clipse, Nneka, Jay Electronica, Tune-Yards, Das Racist and more - including a performance by Wu-Tang members Raekwon, Method Man and Ghostface.

Of course, The Roots couldn&acute;t just throw a music festival with their favorite acts and not grace the stage. The hometown heroes will be performing two sets of their unique, high-energy live sound.

Live music will be playing from two stages during this all-day event.',
	"post_images" 	=> array(
        $dummy_image_url . "event11.webp",
        $dummy_image_url . "music4.webp",
        $dummy_image_url . "music5.webp",
        $dummy_image_url . "music6.webp",
        $dummy_image_url . "music7.webp",
        $dummy_image_url . "event1.webp",
		$dummy_image_url . "event2.webp",
		$dummy_image_url . "event3.webp",
        $dummy_image_url . "event17.webp",
    ),
	"post_category" =>  array( 'Festivals', 'Food & Drink' ) ,
	"post_tags"     => array( 'picnic', 'food' ),
	"video"         => '',
	"phone"       	=> '(000) 111-9999',
	"email"         => 'info@okayplayer.com',
	"website"       => 'http://www.okayplayer.com/rootspicnic',
	"twitter"       => 'http://twitter.com/okayplayer',
	"facebook"      => 'http://facebook.com/okayplayer',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+8 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '10:10',
		'end_time' 		=> '12:10',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);
$dummy_posts[] = array(
	"post_type" 	=> $post_type,
	"post_title" 	=> 'Revolutionary Germantown Festival',
	"post_images" 	=> array(
        $dummy_image_url . "music10.webp",
        $dummy_image_url . "event3.webp",
        $dummy_image_url . "event4.webp",
        $dummy_image_url . "event5.webp",
        $dummy_image_url . "music9.webp",
        $dummy_image_url . "music4.webp",
		$dummy_image_url . "music5.webp",
		$dummy_image_url . "event2.webp",
		$dummy_image_url . "event1.webp",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'germantown', 'rittenhouse' ),
	"post_content"         => 'You are never far from history when in Germantown, one of Philadelphia&acute;s most historic neighborhoods. However, it is on full display during the Revolutionary Germantown Festival, a day-long festival that celebrates the rich history of Germantown and features the annual reenactment of the Battle of Germantown, the only military battle ever fought within the borders of Philadelphia.

Escorted bus and walking tours make getting around simple while special programs at ten historic sites throughout the community provide something for every size and taste.

Learn the inside stories of some of Philadelphia&acute;s most important colonial landmarks: put your hand to colonial paper-making techniques at Historic Rittenhouse Town; try out some early American toys at Upsala; and “meet” British General Howe at the Deshler-Morris House, his one-time war headquarters. The historic re-enactment of the 1777 Battle of Germantown takes place at Cliveden.

In addition to Rittenhouse Town, Upsala, the Deschler-Morris House and Clivedon, you&acute;ll visit the Concord School and Upper Burying Ground, where solider and officers are buried; Grumblethorpe, site of one of the battles legendary death scenes; the Johnson House, which showcase the role of African-Americans in the Revolutionary War; and two of the cities most famous colonial houses, Stenton and Wyck.
<h3>Come Prepared </h3>

There is fee for entry and parking may be limited. It is recommended that visitors consider taking public transportation to Germantown Avenue for the festivities.
<h3>Don&acute;t Miss </h3>

The battle reenactments at Cliveden are absolute must-sees.
<h3>Outsider&acute;s Tip</h3>

Make the most of Revolutionary Germantown Festival by purchasing a Passport that covers the cost of admission to all participating sites for the day. The Passport contains a list of the timed events throughout the day along with a map for self guided walking tours of the Germantown area. Passports can be pre-ordered or purchased the day of the event. An individual pass is $15 and the family pass is $25.',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@peddlersvillage.com',
	"website"       => 'http://www.peddlersvillage.com',
	"twitter"       => 'http://twitter.com/peddlersvillage',
	"facebook"      => 'http://facebook.com/peddlersvillage',
	"recurring"		=> 0,
	"event_dates"	=> array(
		'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
		'end_date' 		=> '',
		'start_time' 	=> '09:00',
		'end_time' 		=> '16:00',
		'all_day' 		=> '',
	),
	"post_dummy"    => '1'
);


/// new dummy data
$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Summer Jazz Fest',
    "post_content" 	=> '<h3>Experience Smooth Jazz Under the Stars</h3>

Indulge in an evening of smooth jazz melodies at the annual Summer Jazz Fest in the heart of the city. Set against the backdrop of the starlit sky, this festival promises to captivate your senses with soulful performances from renowned jazz artists.

From classic standards to contemporary hits, immerse yourself in the rich tapestry of jazz music as you sway to the rhythm and unwind with friends and family. Delight in delectable cuisine from local food vendors and sip on refreshing beverages as you soak up the ambiance of this enchanting event.

Whether you\'re a jazz enthusiast or simply looking for a memorable night out, the Summer Jazz Fest offers an unforgettable experience for all music lovers.

Additional Information:

Admission is free for all attendees. Don\'t miss out on this unforgettable evening of music and entertainment!',
    "post_images" 	=> array(
        $dummy_image_url . "music6.webp",
        $dummy_image_url . "music4.webp",
        $dummy_image_url . "music5.webp",
        $dummy_image_url . "music7.webp",
        $dummy_image_url . "music8.webp",
        $dummy_image_url . "music9.webp",
        $dummy_image_url . "music10.webp",
        $dummy_image_url . "music1.webp",
        $dummy_image_url . "music2.webp",
    ),
    "post_category" =>  array( 'Music','Festivals' ) ,
    "post_tags"     => array( 'jazz', 'music festival' ),
    "video"         => '',
    "phone"       	=> '(000) 333-4444',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/summerjazzfest-dummy-test',
    "twitter"       => 'https://twitter.com/summerjazzfest-dummy-test',
    "facebook"      => 'https://facebook.com/summerjazzfest-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '18:00',
        'end_time' 		=> '23:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Rocktoberfest',
    "post_content" 	=> '<h3>Get Ready to Rock!</h3>

Gear up for a rockin\' good time at Rocktoberfest, the ultimate celebration of all things rock music. This electrifying event brings together top bands, solo artists, and tribute acts for an epic showcase of rock \'n\' roll classics.

From head-banging anthems to soul-stirring ballads, experience the raw energy and passion of rock music like never before. Indulge in mouthwatering food and ice-cold drinks from our variety of vendors, and browse through merchandise stalls for rock-themed goodies.

Whether you\'re a die-hard fan or just looking for a fun night out, Rocktoberfest guarantees an unforgettable experience filled with music, food, and camaraderie.

Additional Information:

Admission is free for all rock enthusiasts. Join us for a night of pure rock \'n\' roll bliss!',
    "post_images" 	=> array(
        $dummy_image_url . "music9.webp",
        $dummy_image_url . "music10.webp",
        $dummy_image_url . "music4.webp",
        $dummy_image_url . "music5.webp",
        $dummy_image_url . "music6.webp",
        $dummy_image_url . "music7.webp",
        $dummy_image_url . "music8.webp",
        $dummy_image_url . "music1.webp",
        $dummy_image_url . "music2.webp",
    ),
    "post_category" =>  array( 'Music','Festivals' ) ,
    "post_tags"     => array( 'rock', 'music festival' ),
    "video"         => '',
    "phone"       	=> '(000) 555-6666',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/rocktoberfest-dummy-test',
    "twitter"       => 'https://twitter.com/rocktoberfest-dummy-test',
    "facebook"      => 'https://facebook.com/rocktoberfest-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+21 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '17:00',
        'end_time' 		=> '22:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Classical Serenade',
    "post_content" 	=> '<h3>An Evening of Classical Elegance</h3>

Step into a world of refined elegance at the Classical Serenade, an enchanting evening of classical music performed by world-class musicians. Let the soothing melodies of Mozart, Beethoven, and Bach transport you to a realm of timeless beauty and sophistication.

Immerse yourself in the sublime atmosphere of the concert hall as you witness virtuoso performances and symphonic masterpieces. Indulge in delectable hors d\'oeuvres and fine wines during intermission, and mingle with fellow aficionados of classical music.

Whether you\'re a seasoned connoisseur or a newcomer to the genre, the Classical Serenade promises an unforgettable experience of musical excellence and cultural refinement.

Additional Information:

Tickets are available for purchase online or at the venue box office. Don\'t miss this opportunity to experience the magic of classical music!',
    "post_images" 	=> array(
        $dummy_image_url . "music2.webp",
        $dummy_image_url . "music3.webp",
        $dummy_image_url . "music7.webp",
        $dummy_image_url . "music8.webp",
        $dummy_image_url . "music9.webp",
        $dummy_image_url . "music10.webp",
        $dummy_image_url . "music4.webp",
        $dummy_image_url . "music5.webp",
        $dummy_image_url . "music6.webp",

    ),
    "post_category" =>  array( 'Music' ) ,
    "post_tags"     => array( 'classical', 'concert' ),
    "video"         => '',
    "phone"       	=> '(000) 777-8888',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/classicalserenade-dummy-test',
    "twitter"       => 'https://twitter.com/classicalserenade-dummy-test',
    "facebook"      => 'https://facebook.com/classicalserenade-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+28 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '19:30',
        'end_time' 		=> '22:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

// sports
$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Summer Soccer Showdown',
    "post_content" 	=> '<h3>Get Ready for a Kickin\' Good Time!</h3>

Join us for the Summer Soccer Showdown, where teams from around the region will compete for glory on the field. Experience the thrill of the beautiful game as skilled athletes showcase their talent and passion for soccer.

Whether you\'re a die-hard fan or new to the sport, this event promises non-stop action and excitement. Cheer on your favorite teams, indulge in delicious stadium snacks, and enjoy family-friendly activities throughout the day.

Don\'t miss out on the biggest soccer event of the summer! Grab your tickets now and be part of the Summer Soccer Showdown.

Additional Information:

Tickets are available online or at the venue box office. Get ready to kick off an unforgettable day of soccer fun!',
    "post_images" 	=> array(
        $dummy_image_url . "sport5.webp",
        $dummy_image_url . "sport4.webp",
        $dummy_image_url . "sport6.webp",
        $dummy_image_url . "sport2.webp",
        $dummy_image_url . "sport8.webp",
        $dummy_image_url . "sport1.webp",
        $dummy_image_url . "sport9.webp",
        $dummy_image_url . "sport3.webp",
        $dummy_image_url . "sport7.webp",
    ),
    "post_category" =>  array( 'Sport' ) ,
    "post_tags"     => array( 'soccer', 'tournament' ),
    "video"         => '',
    "phone"       	=> '(000) 123-4567',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/summersoccershowdown-dummy-test',
    "twitter"       => 'https://twitter.com/summersoccer-dummy-test',
    "facebook"      => 'https://facebook.com/summersoccershown-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '09:00',
        'end_time' 		=> '18:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Basketball Bonanza',
    "post_content" 	=> '<h3>Dribble, Shoot, Score!</h3>

Get ready for the ultimate basketball showdown at the Basketball Bonanza! Join us as teams from across the country compete for the championship title in an electrifying display of skill and athleticism.

Experience the adrenaline rush of fast-paced action, jaw-dropping dunks, and clutch three-pointers as players battle it out on the court. Whether you\'re a basketball fanatic or just looking for a fun day out, the Basketball Bonanza promises excitement for all ages.

Cheer on your favorite teams, enjoy delicious concessions, and participate in interactive fan activities throughout the day. Don\'t miss your chance to witness basketball history in the making!

Additional Information:

Tickets are available online or at the venue box office. Secure your seat today and be part of the Basketball Bonanza!',
    "post_images" 	=> array(
        $dummy_image_url . "sport8.webp",
        $dummy_image_url . "sport2.webp",
        $dummy_image_url . "sport1.webp",
        $dummy_image_url . "sport3.webp",
        $dummy_image_url . "sport5.webp",
        $dummy_image_url . "sport7.webp",
        $dummy_image_url . "sport9.webp",
        $dummy_image_url . "sport6.webp",
        $dummy_image_url . "sport4.webp",
    ),
    "post_category" =>  array( 'Sport' ) ,
    "post_tags"     => array( 'basketball', 'tournament' ),
    "video"         => '',
    "phone"       	=> '(000) 456-7890',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/basketballbonanza-dummy-test',
    "twitter"       => 'https://twitter.com/basketballbon-dummy-test',
    "facebook"      => 'https://facebook.com/basketballbonanza-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+21 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '10:00',
        'end_time' 		=> '20:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Summer Surfing Spectacular',
    "post_content" 	=> '<h3>Hang Ten and Catch Some Waves!</h3>

Ride the waves at the Summer Surfing Spectacular, where surfers of all skill levels gather for a day of sun, sand, and surf. Whether you\'re a seasoned pro or a beginner hitting the waves for the first time, this event offers something for everyone.

Experience the thrill of riding the perfect wave as you compete in various surfing competitions, including longboard, shortboard, and bodyboarding contests. Spectators can cheer on their favorite surfers from the shore while soaking up the beach vibes and enjoying live music and beachside entertainment.

Don\'t miss out on the hottest surfing event of the summer! Grab your board and join us for a day of endless summer fun at the Summer Surfing Spectacular.

Additional Information:

Registration is open to all surfers. Visit our website to sign up and reserve your spot in the competition!',
    "post_images" 	=> array(
        $dummy_image_url . "sport3.webp",
        $dummy_image_url . "sport7.webp",
        $dummy_image_url . "sport9.webp",
        $dummy_image_url . "sport4.webp",
        $dummy_image_url . "sport1.webp",
        $dummy_image_url . "sport6.webp",
        $dummy_image_url . "sport2.webp",
        $dummy_image_url . "sport5.webp",
        $dummy_image_url . "sport8.webp",
    ),
    "post_category" =>  array( 'Sport' ) ,
    "post_tags"     => array( 'surfing', 'competition' ),
    "video"         => '',
    "phone"       	=> '(000) 789-0123',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/summersurfing-dummy-test',
    "twitter"       => 'https://twitter.com/summersurf-dummy-test',
    "facebook"      => 'https://facebook.com/summersurfing-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+28 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '08:00',
        'end_time' 		=> '18:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Entrepreneurship Summit',
    "post_content" 	=> '<h3>Ignite Your Entrepreneurial Spirit!</h3>

Join us for the Entrepreneurship Summit, where aspiring entrepreneurs and seasoned business leaders come together to share insights, strategies, and success stories. Whether you\'re launching a startup or looking to grow your business, this summit offers valuable resources and networking opportunities to fuel your entrepreneurial journey.

Gain inspiration from keynote speakers who have built successful businesses from the ground up, and participate in interactive workshops and panel discussions covering topics such as marketing, funding, and innovation. Connect with mentors and industry experts who can provide guidance and support as you navigate the challenges of entrepreneurship.

Don\'t miss this chance to learn, network, and collaborate with like-minded individuals who share your passion for business and innovation. Register now and take the next step toward realizing your entrepreneurial dreams!

Additional Information:

Tickets are available for purchase online or at the venue. Reserve your spot today and unlock the tools and knowledge you need to succeed!',
    "post_images" 	=> array(
        $dummy_image_url . "business2.webp",
        $dummy_image_url . "business1.webp",
        $dummy_image_url . "business3.webp",
        $dummy_image_url . "business4.webp",
        $dummy_image_url . "business5.webp",
        $dummy_image_url . "business6.webp",
        $dummy_image_url . "business7.webp",
        $dummy_image_url . "business8.webp",
        $dummy_image_url . "business9.webp",
    ),
    "post_category" =>  array( 'Business' ) ,
    "post_tags"     => array( 'entrepreneurship', 'summit' ),
    "video"         => '',
    "phone"       	=> '(000) 123-4567',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/entrepreneurshipsummit-dummy-test',
    "twitter"       => 'https://twitter.com/entrepreneursum-dummy-test',
    "facebook"      => 'https://facebook.com/entrepreneurshipsummit-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '09:00',
        'end_time' 		=> '17:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Tech Innovation Conference',
    "post_content" 	=> '<h3>Explore the Future of Technology!</h3>

Discover the latest trends and innovations shaping the tech industry at the Tech Innovation Conference. From artificial intelligence and blockchain to cybersecurity and cloud computing, this conference covers a wide range of topics designed to inspire, educate, and empower technology enthusiasts and industry professionals.

Join leading experts and visionaries as they share their insights and expertise through keynote presentations, panel discussions, and hands-on workshops. Network with fellow attendees and explore cutting-edge technologies showcased by exhibitors and sponsors.

Whether you\'re a tech entrepreneur, developer, or enthusiast, the Tech Innovation Conference offers a unique opportunity to connect with the brightest minds in the industry and stay ahead of the curve in today\'s rapidly evolving tech landscape.

Additional Information:

Tickets are available for purchase online or at the venue. Reserve your spot now and unlock access to a world of innovation and opportunity!',
    "post_images" 	=> array(
        $dummy_image_url . "business9.webp",
        $dummy_image_url . "business2.webp",
        $dummy_image_url . "business1.webp",
        $dummy_image_url . "business5.webp",
        $dummy_image_url . "business8.webp",
        $dummy_image_url . "business3.webp",
        $dummy_image_url . "business6.webp",
        $dummy_image_url . "business4.webp",
        $dummy_image_url . "business7.webp",
    ),
    "post_category" =>  array( 'Business' ) ,
    "post_tags"     => array( 'technology', 'innovation', 'conference' ),
    "video"         => '',
    "phone"       	=> '(000) 234-5678',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/techinnovationconf-dummy-test',
    "twitter"       => 'https://twitter.com/techinnovation-dummy-test',
    "facebook"      => 'https://facebook.com/techinnovationconf-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+21 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '08:30',
        'end_time' 		=> '18:30',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Finance Mastery Workshop',
    "post_content" 	=> '<h3>Unlock the Secrets to Financial Success!</h3>

Take control of your financial future at the Finance Mastery Workshop, where experts will guide you through proven strategies for building wealth, managing investments, and achieving financial independence. Whether you\'re a seasoned investor or just starting your journey to financial freedom, this workshop offers practical insights and actionable advice to help you reach your goals.

Learn from industry-leading professionals as they share their expertise on topics such as budgeting, saving, investing, and retirement planning. Gain valuable insights into market trends and economic indicators that can help you make informed financial decisions.

Don\'t miss this opportunity to invest in yourself and secure a brighter financial future. Register now and take the first step toward mastering your finances!

Additional Information:

Tickets are available for purchase online or at the venue. Reserve your seat today and embark on a journey to financial empowerment!',
    "post_images" 	=> array(
        $dummy_image_url . "business4.webp",
        $dummy_image_url . "business5.webp",
        $dummy_image_url . "business8.webp",
        $dummy_image_url . "business6.webp",
        $dummy_image_url . "business3.webp",
        $dummy_image_url . "business2.webp",
        $dummy_image_url . "business1.webp",
        $dummy_image_url . "business9.webp",
        $dummy_image_url . "business7.webp",
    ),
    "post_category" =>  array( 'Business' ) ,
    "post_tags"     => array( 'finance', 'workshop' ),
    "video"         => '',
    "phone"       	=> '(000) 345-6789',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/financemasteryworkshop-dummy-test',
    "twitter"       => 'https://twitter.com/financemastery-dummy-test',
    "facebook"      => 'https://facebook.com/financemasteryworkshop-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+28 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '10:00',
        'end_time' 		=> '16:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

// Health & Beauty
$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Wellness Retreat',
    "post_content" 	=> '<h3>Revitalize Your Mind, Body, and Soul!</h3>

Embark on a journey of self-discovery and renewal at the Wellness Retreat, where you\'ll experience a holistic approach to health and well-being. Escape the hustle and bustle of everyday life and immerse yourself in a serene oasis of relaxation and rejuvenation.

Indulge in a variety of wellness activities designed to nourish your body, calm your mind, and uplift your spirit. From yoga and meditation to spa treatments and nature walks, each experience is crafted to promote balance and harmony in your life.

Reconnect with yourself and embrace a healthier lifestyle as you learn from wellness experts and engage in meaningful conversations with fellow participants. Leave feeling refreshed, renewed, and inspired to make positive changes in your life.

Additional Information:

Spaces are limited, so reserve your spot today to embark on a transformative journey of wellness and self-care!',
    "post_images" 	=> array(
        $dummy_image_url . "health_beauty9.webp",
        $dummy_image_url . "health_beauty8.webp",
        $dummy_image_url . "health_beauty4.webp",
        $dummy_image_url . "health_beauty5.webp",
        $dummy_image_url . "health_beauty6.webp",
        $dummy_image_url . "health_beauty7.webp",
        $dummy_image_url . "health_beauty1.webp",
        $dummy_image_url . "health_beauty2.webp",
        $dummy_image_url . "health_beauty3.webp",

    ),
    "post_category" =>  array( 'Health & Beauty' ) ,
    "post_tags"     => array( 'wellness', 'retreat' ),
    "video"         => '',
    "phone"       	=> '(000) 123-4567',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/wellnessretreat-dummy-test',
    "twitter"       => 'https://twitter.com/wellnessretreat-dummy-test',
    "facebook"      => 'https://facebook.com/wellnessretreat-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '09:00',
        'end_time' 		=> '17:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Beauty Expo',
    "post_content" 	=> '<h3>Celebrate Beauty in All Its Forms!</h3>

Join us at the Beauty Expo, where industry professionals and beauty enthusiasts come together to celebrate the latest trends, products, and innovations in the world of beauty. From skincare and makeup to haircare and wellness, this expo offers something for everyone who loves all things beauty.

Experience live demonstrations from top beauty brands, discover new techniques from expert makeup artists, and shop exclusive deals on your favorite beauty products. Whether you\'re a beauty professional or simply passionate about self-care, the Beauty Expo is the ultimate destination to indulge in all things beauty.

Don\'t miss out on this opportunity to connect with fellow beauty lovers, explore new trends, and celebrate the power of beauty in transforming lives.

Additional Information:

Tickets are available for purchase online or at the venue. Reserve your ticket now and immerse yourself in the world of beauty!',
    "post_images" 	=> array(
        $dummy_image_url . "health_beauty3.webp",
        $dummy_image_url . "health_beauty1.webp",
        $dummy_image_url . "health_beauty2.webp",
        $dummy_image_url . "health_beauty4.webp",
        $dummy_image_url . "health_beauty2.webp",
        $dummy_image_url . "health_beauty6.webp",
        $dummy_image_url . "health_beauty8.webp",
        $dummy_image_url . "health_beauty9.webp",
        $dummy_image_url . "health_beauty5.webp",
    ),
    "post_category" =>  array( 'Health & Beauty' ) ,
    "post_tags"     => array( 'beauty', 'expo' ),
    "video"         => '',
    "phone"       	=> '(000) 234-5678',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/beautyexpo-dummy-test',
    "twitter"       => 'https://twitter.com/beautyexpo-dummy-test',
    "facebook"      => 'https://facebook.com/beautyexpo-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+21 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '08:30',
        'end_time' 		=> '18:30',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

$dummy_posts[] = array(
    "post_type" 	=> $post_type,
    "post_title" 	=> 'Yoga and Meditation Retreat',
    "post_content" 	=> '<h3>Find Inner Peace and Balance!</h3>

Escape the stresses of daily life and reconnect with your inner self at the Yoga and Meditation Retreat. Immerse yourself in the ancient practices of yoga and meditation as you journey toward greater mindfulness, relaxation, and spiritual awakening.

Experience daily yoga sessions led by experienced instructors, designed to improve flexibility, strength, and mental clarity. Learn meditation techniques to calm the mind and cultivate inner peace, and discover the transformative power of breathwork and mindfulness practices.

Surrounded by the serene beauty of nature, you\'ll find tranquility and rejuvenation as you embark on this soul-nourishing retreat. Leave feeling refreshed, renewed, and ready to embrace life with a newfound sense of balance and purpose.

Additional Information:

Spaces are limited, so reserve your spot today and embark on a journey of self-discovery and inner transformation!',
    "post_images" 	=> array(
        $dummy_image_url . "health_beauty8.webp",
        $dummy_image_url . "health_beauty7.webp",
        $dummy_image_url . "health_beauty5.webp",
        $dummy_image_url . "health_beauty6.webp",
        $dummy_image_url . "health_beauty2.webp",
        $dummy_image_url . "health_beauty3.webp",
        $dummy_image_url . "health_beauty1.webp",
        $dummy_image_url . "health_beauty9.webp",
        $dummy_image_url . "health_beauty4.webp",
    ),
    "post_category" =>  array( 'Health & Beauty' ) ,
    "post_tags"     => array( 'yoga', 'meditation', 'retreat' ),
    "video"         => '',
    "phone"       	=> '(000) 345-6789',
    "email"         => 'info@example.com',
    "website"       => 'https://example.com/yogameditationretreat-dummy-test',
    "twitter"       => 'https://twitter.com/yogameditation-dummy-test',
    "facebook"      => 'https://facebook.com/yogameditationretreat-dummy-test',
    "recurring"		=> 0,
    "event_dates"	=> array(
        'start_date' 	=> date_i18n( 'Y-m-d', strtotime( "+28 days" ) ),
        'end_date' 		=> '',
        'start_time' 	=> '10:00',
        'end_time' 		=> '16:00',
        'all_day' 		=> '',
    ),
    "post_dummy"    => '1'
);

 
 
function geodir_event_extra_custom_fields_standard_events( $fields, $post_type, $package_id ) {
	if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		return $fields;
	}

	return $fields;
}