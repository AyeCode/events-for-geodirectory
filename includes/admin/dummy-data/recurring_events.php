<?php
/**
 * GeoDirectory Events dummy data for recurring events.
 *
 * @since 2.0.0
 * @package GeoDirectory_Event_Manager
 */

global $dummy_image_url, $dummy_categories, $dummy_custom_fields, $dummy_posts, $dummy_sort_fields;

$dummy_image_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/';

// Dummy categories
$dummy_categories  = array();
$dummy_categories['food-drink'] = array(
	'name'        => 'Food & Drink',
	'icon'        => $dummy_image_url . 'cat_icon/Food_Nightlife.png',
	'schema_type' => 'FoodEvent'
);
$dummy_categories['festivals'] = array(
	'name'        => 'Festivals',
	'icon'        => $dummy_image_url . 'cat_icon/Festival.png',
	'schema_type' => 'Event'
);

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
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival9.jpg",
		"$dummy_image_url/festival10.jpg",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'italian market', 'italian festival' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@italianmarketfestival.com',
	"website"       => 'http://www.italianmarketfestival.com',
	"twitter"       => 'http://twitter.com/italianmarketfestival',
	"facebook"      => 'http://facebook.com/italianmarketfestival',
	"recurring"		=> 1,
	"event_dates"	=> array(
		'start_date' 		=> date_i18n( 'Y-m-d', strtotime( "+30 days" ) ),
		'end_date' 			=> '',
		'start_time' 		=> '10:00',
		'end_time' 			=> '12:00',
		'all_day' 			=> '',
		'duration_x'		=> '1',
		'repeat_type'		=> 'month',
		'repeat_x'			=> '1',
		'repeat_end_type'	=> '1',
		'max_repeat'		=> '',
		'repeat_end'		=> date_i18n( 'Y-m-d', strtotime( "+4 months" ) ),
		'recurring_dates'	=> '',
		'different_times'	=> '',
		'start_times'		=> '',
		'end_times'			=> '',
		'repeat_days'		=> array( '6' ),
		'repeat_weeks'		=> array( '1', '3' )
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
		"$dummy_image_url/restaurants4.jpg",
		"$dummy_image_url/restaurants5.jpg",
		"$dummy_image_url/restaurants6.jpg",
		"$dummy_image_url/restaurants7.jpg",
		"$dummy_image_url/restaurants8.jpg",
		"$dummy_image_url/restaurants9.jpg",
		"$dummy_image_url/restaurants10.jpg",
		"$dummy_image_url/restaurants1.jpg",
		"$dummy_image_url/restaurants2.jpg",
		"$dummy_image_url/restaurants3.jpg",
	),
	"post_category" =>  array( 'Food & Drink' ) ,
	"post_tags"     => array( 'caribbean food' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@pennslandingcorp.com',
	"website"       => 'http://www.pennslandingcorp.com',
	"twitter"       => 'http://twitter.com/pennslandingcorp',
	"facebook"      => 'http://facebook.com/pennslandingcorp',
	"recurring"		=> 1,
	"event_dates"	=> array(
		'start_date' 		=> date_i18n( 'Y-m-d', strtotime( "+7 days" ) ),
		'end_date' 			=> '',
		'start_time' 		=> '08:00',
		'end_time' 			=> '21:00',
		'all_day' 			=> '',
		'duration_x'		=> '1',
		'repeat_type'		=> 'year',
		'repeat_x'			=> '1',
		'repeat_end_type'	=> '0',
		'max_repeat'		=> '3',
		'repeat_end'		=> '',
		'recurring_dates'	=> '',
		'different_times'	=> '',
		'start_times'		=> '',
		'end_times'			=> '',
		'repeat_days'		=> '',
		'repeat_weeks'		=> ''
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
		"$dummy_image_url/festival10.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
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
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival2.jpg",
	),
	"post_category" =>  array( 'Food & Drink', 'Festivals' ) ,
	"post_tags"     => array( 'caribbean' ),
	"video"         => '',
	"phone"       	=> '(000) 111-2222',
	"email"         => 'info@pennslandingcorp.com',
	"website"       => 'http://www.pennslandingcorp.com',
	"twitter"       => 'http://twitter.com/pennslandingcorp',
	"facebook"      => 'http://facebook.com/pennslandingcorp',
	"recurring"		=> 1,
	"event_dates"	=> array(
		'start_date' 		=> date_i18n( 'Y-m-d', strtotime( "+5 days" ) ),
		'end_date' 			=> '',
		'start_time' 		=> '',
		'end_time' 			=> '',
		'all_day' 			=> '1',
		'duration_x'		=> '1',
		'repeat_type'		=> 'week',
		'repeat_x'			=> '1',
		'repeat_end_type'	=> '0',
		'max_repeat'		=> '7',
		'repeat_end'		=> '',
		'recurring_dates'	=> '',
		'different_times'	=> '',
		'start_times'		=> '',
		'end_times'			=> '',
		'repeat_days'		=> array( '0' ),
		'repeat_weeks'		=> ''
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
		"$dummy_image_url/restaurants2.jpg",
		"$dummy_image_url/restaurants4.jpg",
		"$dummy_image_url/restaurants6.jpg",
		"$dummy_image_url/restaurants8.jpg",
		"$dummy_image_url/restaurants10.jpg",
		"$dummy_image_url/restaurants1.jpg",
		"$dummy_image_url/restaurants3.jpg",
		"$dummy_image_url/restaurants5.jpg",
		"$dummy_image_url/restaurants7.jpg",
		"$dummy_image_url/restaurants9.jpg",
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
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
		"$dummy_image_url/festival10.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival4.jpg",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'dutch festival', 'woodcrafts' ),
	"video"         => '',
	"phone"       	=> '(000) 111-4444',
	"email"         => 'info@readingterminalmarket.com',
	"website"       => 'http://www.readingterminalmarket.com',
	"twitter"       => 'http://twitter.com/readingterminalmarket',
	"facebook"      => 'http://facebook.com/readingterminalmarket',
	"recurring"		=> 1,
	"event_dates"	=> array(
		'start_date' 		=> date_i18n( 'Y-m-d', strtotime( "+10 days" ) ),
		'end_date' 			=> '',
		'start_time' 		=> '10:30',
		'end_time' 			=> '12:30',
		'all_day' 			=> '',
		'duration_x'		=> '1',
		'repeat_type'		=> 'day',
		'repeat_x'			=> '2',
		'repeat_end_type'	=> '0',
		'max_repeat'		=> '5',
		'repeat_end'		=> '',
		'recurring_dates'	=> '',
		'different_times'	=> '',
		'start_times'		=> '',
		'end_times'			=> '',
		'repeat_days'		=> '',
		'repeat_weeks'		=> ''
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
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
		"$dummy_image_url/festival10.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival3.jpg",
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
		"$dummy_image_url/restaurants7.jpg",
		"$dummy_image_url/restaurants11.jpg",
		"$dummy_image_url/restaurants10.jpg",
		"$dummy_image_url/restaurants3.jpg",
		"$dummy_image_url/restaurants1.jpg",
		"$dummy_image_url/restaurants5.jpg",
		"$dummy_image_url/restaurants6.jpg",
		"$dummy_image_url/restaurants8.jpg",
		"$dummy_image_url/restaurants9.jpg",
		"$dummy_image_url/restaurants2.jpg",
		"$dummy_image_url/restaurants4.jpg"
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
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival10.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival5.jpg",
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
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
	"post_content" 	=> '',
	"post_images" 	=> array(
		"$dummy_image_url/festival7.jpg",
		"$dummy_image_url/festival9.jpg",
		"$dummy_image_url/festival10.jpg",
		"$dummy_image_url/festival8.jpg",
		"$dummy_image_url/festival6.jpg",
		"$dummy_image_url/festival4.jpg",
		"$dummy_image_url/festival2.jpg",
		"$dummy_image_url/festival1.jpg",
		"$dummy_image_url/festival3.jpg",
		"$dummy_image_url/festival5.jpg",
	),
	"post_category" =>  array( 'Festivals' ) ,
	"post_tags"     => array( 'germantown', 'rittenhouse' ),
	"video"         => 'You are never far from history when in Germantown, one of Philadelphia&acute;s most historic neighborhoods. However, it is on full display during the Revolutionary Germantown Festival, a day-long festival that celebrates the rich history of Germantown and features the annual reenactment of the Battle of Germantown, the only military battle ever fought within the borders of Philadelphia.

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
	"recurring"		=> 1,
	"event_dates"	=> array(
		'start_date' 		=> date_i18n( 'Y-m-d', strtotime( "+14 days" ) ),
		'end_date' 			=> '',
		'start_time' 		=> '09:00',
		'end_time' 			=> '16:00',
		'all_day' 			=> '',
		'duration_x'		=> '1',
		'repeat_type'		=> 'custom',
		'repeat_x'			=> '',
		'repeat_end_type'	=> '0',
		'max_repeat'		=> '',
		'repeat_end'		=> '',
		'recurring_dates'	=> array( date_i18n( 'Y-m-d', strtotime( "+6 days" ) ), date_i18n( 'Y-m-d', strtotime( "+9 days" ) ), date_i18n( 'Y-m-d', strtotime( "+13 days" ) ) ),
		'different_times'	=> '1',
		'start_times'		=> array( '09:30', '10:00', '10:30' ),
		'end_times'			=> array( '16:30', '17:00', '17:30' ),
		'repeat_days'		=> '',
		'repeat_weeks'		=> ''
	),
	"post_dummy"    => '1'
);
 
 
function geodir_event_extra_custom_fields_recurring_events( $fields, $post_type, $package_id ) {
	if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		return $fields;
	}

	return $fields;
}