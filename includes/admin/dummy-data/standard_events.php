<?php
/**
 * GD Standard Dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */


/// Festival ////post start 1//

global $dummy_post_index,$city_bound_lat1,$city_bound_lng1,$city_bound_lat2,$city_bound_lng2;
$post_info = array();
$image_array = array();
$post_meta = array();

if($dummy_post_index==1){
	geodir_event_default_taxonomies();
	geodir_event_display_filter_options();
}

if(geodir_event_dummy_folder_exists())
	$dummy_image_url = geodir_event_plugin_url(). "/gdevents-admin/dummy";
else
	$dummy_image_url = 'http://wpgeodirectory.com/dummy_event';

$dummy_image_url = apply_filters('event_dummy_image_url', $dummy_image_url);
	
	
switch($dummy_post_index) 
{

	case(1):
	
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		$image_array[] = "$dummy_image_url/festival3.jpg";
		$image_array[] = "$dummy_image_url/festival4.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival6.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival10.jpg";
		$image_array[] = "$dummy_image_url/festival11.jpg";
		
		$post_info[] = array(
					"listing_type"	=> 'gd_event',
					"post_title"	=>	'Street Italian Market Festival',
					"post_desc"	=>	'<h3>The Experience </h3>

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

There is so much great eating in and around the Italian Market that you&acute;ll want to return again and again. 
',
          "post_images"	=>	$image_array,
					"post_category"	=>	array('gd_eventcategory'  => array('Events')),
					"post_tags"		=>	array(''),
					"video"			=> '',
					"geodir_timing"		=> 'Date - May 15-16, 2010',
					"geodir_contact"		=> '(000) 111-2222',
					"geodir_email"			=> 'info@italianmarketfestival.com',
					"geodir_website"		=> 'http://www.italianmarketfestival.com/',
					"geodir_twitter"		=> 'http://twitter.com/italianmarketfestival',
					"geodir_facebook"		=> 'http://facebook.com/italianmarketfestival',
					"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+30 days")),
					"starttime"					=> '10:00 AM',
					"endtime"						=> '12:00 PM',
					"post_dummy" 	=> '1'
					);
		
	break;		
	
	case(2):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival10.jpg";
		$image_array[] = "$dummy_image_url/festival6.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival10.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Festival, Concert and Fireworks',
							"post_desc"	=>	'
		This Fourth of July, celebrate America independence with incredible fireworks in Philadelphia during the annual Wawa Welcome America! festival!
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
		
		Bring lawn chairs, a blanket and a picnic while you watch the parade. Then stay for the concert and fireworks. If you arrive early, you&acute;ll be able to grab a great location for viewing all three.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Fireworks'),
							"video"			=> '',
							"geodir_timing"		=> 'July 4, 2010 | 11 a.m. – 11 p.m.',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@italianmarketfestival.com',
							"geodir_website"		=> 'http://www.italianmarketfestival.com/',
							"geodir_twitter"		=> 'http://twitter.com/italianmarketfestival',
							"geodir_facebook"		=> 'http://facebook.com/italianmarketfestival',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+4 days")),
							"starttime"					=> '11:00 AM',
							"endtime"						=> '3:00 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(3):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival6.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival10.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Caribbean Festival',
							"post_desc"	=>	'
		<h3>The Experience</h3>
		
		Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.
		
		With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.
		
		Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.
		
		In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
		Additional Information
		
		Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Fireworks'),
							"video"			=> '',
							"geodir_timing"		=> 'August 22, 2010; 2-8 p.m.',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@pennslandingcorp.com',
							"geodir_website"		=> 'http://www.pennslandingcorp.com/',
							"geodir_twitter"		=> 'http://twitter.com/pennslandingcorp',
							"geodir_facebook"		=> 'http://facebook.com/pennslandingcorp',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+5 days")),
							"starttime"					=> '12:00 PM',
							"endtime"						=> '6:00 PM',		
							"post_dummy" 	=> '1'
							);
	break;

	case(4):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival11.jpg";
		$image_array[] = "$dummy_image_url/festival6.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Caribbean New',
							"post_desc"	=>	'
		<h3>The Experience</h3>
		
		Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.
		
		With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.
		
		Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.
		
		In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
		Additional Information
		
		Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array(''),
							"video"			=> '',
							"geodir_timing"		=> 'August 22, 2010; 2-8 p.m.',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@pennslandingcorp.com',
							"geodir_website"		=> 'http://www.pennslandingcorp.com/',
							"geodir_twitter"		=> 'http://twitter.com/pennslandingcorp',
							"geodir_facebook"		=> 'http://facebook.com/pennslandingcorp',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+7 days")),
							"starttime"					=> '8:00 AM',
							"endtime"						=> '9:00 PM',	
							"post_dummy" 	=> '1'
							);
	break;
	
	case(5):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival12.jpg";
		$image_array[] = "$dummy_image_url/festival13.jpg";
		$image_array[] = "$dummy_image_url/festival14.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Kennett Square Mushroom Festival',
							"post_desc"	=>	'
		<h3>The Experience</h3>
		
		Travel to the Islands without leaving Philadelphia for the 25th annual Caribbean Festival at Penn&acute;s Landing Great Plaza. This free festival of Caribbean traditions, music and food is a culturally rich celebration of 14 Caribbean Islands featuring a collage of sights, sounds, aromas and tastes.
		
		With entertainment as the focal point of the event, you&acute;ll be surrounded by the authentic island sounds of reggae, soca/calypso, hip-hop and gospel. There will also be creative dances, ethnic poetry and educational activities.
		
		Fragrant aromas will fill the Great Plaza as the vendors prepare a variety of tempting island cuisine for visitors to enjoy. At the Caribbean marketplace, visitors can browse displays of island fashions, souvenirs and arts and crafts.
		
		In addition, the Caribbean Culture booth will complement this year&acute;s event with featured topics about Caribbean history, fashion and religion. For the youngest attendees, the Festival offers a Caribbean Children&acute;s Village to teach children about the African-Caribbean culture awareness.
		Additional Information
		
		Admission is free for all PECO Multicultural Series events. PECO presents a series of free Multicultural festivals throughout the summer season at the Great Plaza at Penn&acute;s Landing.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Mushroom'),
							"video"			=> '',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@pennslandingcorp.com',
							"geodir_website"		=> 'http://www.pennslandingcorp.com/',
							"geodir_twitter"		=> 'http://twitter.com/pennslandingcorp',
							"geodir_facebook"		=> 'http://facebook.com/pennslandingcorp',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+7 days")),
							"starttime"					=> '10:00 AM',
							"endtime"						=> '12:00 PM',	
							"post_dummy" 	=> '1'
							);
	break;
	
	case(6):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival15.jpg";
		$image_array[] = "$dummy_image_url/festival13.jpg";
		$image_array[] = "$dummy_image_url/festival14.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Reading Terminal Market&acute;s Pennsylvania Dutch Festival',
							"post_desc"	=>	'
		Celebrate the traditions, foods and crafts of the Pennsylvania Dutch at the 21st annual Pennsylvania Dutch Festival at the historic Reading Terminal Market.
		
		The three-day festival will take place in the Market&acute;s center court seating area and will feature handmade crafts including quilts, woodcrafts, paintings, hand braided rugs, wooden toys and cedar chests.
		
		Traditional foods including chicken pot pie, donuts, ice cream, pies and canned fruits and vegetables will be available to taste and purchase.
		
		On Saturday, August 13, the festival moves outdoors to create a country fair in the city. The 1100 block of Arch Street will be closed to traffic and a petting zoo with sheep, goats, chickens, donkeys, calves, horses and pigs will fill the street.
		
		Amish buggy rides and horse drawn wagon rides around the Market, as well as country and bluegrass bands, round out the entertainment for this great, family-friendly event.
		
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Dutch'),
							"video"			=> '',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@readingterminalmarket.com',
							"geodir_website"		=> 'http://www.readingterminalmarket.org/',
							"geodir_twitter"		=> 'http://twitter.com/readingterminalmarket',
							"geodir_facebook"		=> 'http://facebook.com/readingterminalmarket',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+10 days")),
							"starttime"					=> '10:30 AM',
							"endtime"						=> '12:30 PM',	
							"post_dummy" 	=> '1'
							);
	break;
	
	case(7):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival16.jpg";
		$image_array[] = "$dummy_image_url/festival13.jpg";
		$image_array[] = "$dummy_image_url/festival14.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Philadelphia Gay and Lesbian Theatre Festival',
							"post_desc"	=>	'
		The Philadelphia Gay and Lesbian Theatre Festival has been canceled for 2010.
		
		The Seventh Annual Philadelphia Gay and Lesbian Theatre Festival (PGLTF) begins its loud and proud two-week run on June 11, 2009. Several theater productions celebrate the gay, lesbian, bisexual and transgender experience through the art of theater.
		
		The festival typically included both local and international premieres of critically acclaimed dramas, comedies, musicals and one-person shows. All productions aim to entertain, educate, empower, enlighten, challenge and delight audiences.
		
		Topics of previous productions included a musical review of favorite Broadway tunes coming to life with a decidedly gay perspective; dealing with one inner burdens while on a pilgrimage to India; turning the damages of sexual abuse to that which gives rise to transformation; intertwined lives of gay men and the women who love them; delving into whether Shakespeare was bi-sexual and if the subject of his love sonnets was a young boy; as well as two productions specifically presented as a part of our Young Audience Presentations.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Market'),
							"video"			=> '',
							"geodir_contact"		=> '(000) 111-2222',
							"geodir_email"			=> 'info@pgltf.com',
							"geodir_website"		=> 'http://www.pgltf.org/',
							"geodir_twitter"		=> 'http://twitter.com/pgltf',
							"geodir_facebook"		=> 'http://facebook.com/pgltf',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+10 days")),
							"starttime"					=> '11:30 AM',
							"endtime"						=> '3:00 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(8):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival17.jpg";
		$image_array[] = "$dummy_image_url/festival13.jpg";
		$image_array[] = "$dummy_image_url/festival14.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Super Scooper All-You-Can-Eat Ice Cream Festival',
							"post_desc"	=>	'
		<h3>The Experience</h3>
		
		What better way to raise money for children with leukemia than to eat your favorite kind of ice cream?
		
		At Wawa Welcome America!‘s annual Super Scooper All-You-Can-Eat Ice Cream Festival, you can do just that - as well as enjoy free music, live entertainment and games for the whole family!
		
		At this annual celebration of sweetness, more than 20 ice cream and water ice companies will serve up their cool, creamy treats. After paying the $5 admission, ice cream lovers are given a spoon and unlimited access to their favorites. Clearly, this is no time to count calories.
		
		All proceeds from the event will benefit the Joshua Kahan Fund and the fight to cure pediatric leukemia.
		<h3>Additional Information </h3>
		
			
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array(''),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@welcomeamerica.com',
							"geodir_website"		=> 'http://www.welcomeamerica.com/',
							"geodir_twitter"		=> 'http://twitter.com/welcomeamerica',
							"geodir_facebook"		=> 'http://facebook.com/welcomeamerica',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+10 days")),
							"starttime"					=> '10:15 AM',
							"endtime"						=> '12:15 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(9):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival18.jpg";
		$image_array[] = "$dummy_image_url/festival19.jpg";
		$image_array[] = "$dummy_image_url/festival20.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'The Roots Picnic',
							"post_desc"	=>	'
		<h3>Location </h3>
		
		Festival Pier at Penn&acute;s Landing
		Columbus Boulevard at Spring Garden Street
		<h3>The Festival </h3>
		
		The Roots - the Philly natives also known as the Legendary Roots Crew - have gathered a diverse lineup of talent for this third annual music festival, including: Vampire Weekend, Mayer Hawthorne, The Very Best, Clipse, Nneka, Jay Electronica, Tune-Yards, Das Racist and more - including a performance by Wu-Tang members Raekwon, Method Man and Ghostface.
		
		Of course, The Roots couldn&acute;t just throw a music festival with their favorite acts and not grace the stage. The hometown heroes will be performing two sets of their unique, high-energy live sound.
		
		Live music will be playing from two stages during this all-day event.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Picnic'),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@okayplayer.com',
							"geodir_website"		=> 'http://www.okayplayer.com/rootspicnic/',
							"geodir_twitter"		=> 'http://twitter.com/okayplayer',
							"geodir_facebook"		=> 'http://facebook.com/okayplayer',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+10 days")),
							"starttime"					=> '10:10 AM',
							"endtime"						=> '12:10 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(10):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival21.jpg";
		$image_array[] = "$dummy_image_url/festival19.jpg";
		$image_array[] = "$dummy_image_url/festival20.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Revolutionary Germantown Festival',
							"post_desc"	=>	'
		You are never far from history when in Germantown, one of Philadelphia&acute;s most historic neighborhoods. However, it is on full display during the Revolutionary Germantown Festival, a day-long festival that celebrates the rich history of Germantown and features the annual reenactment of the Battle of Germantown, the only military battle ever fought within the borders of Philadelphia.
		
		Escorted bus and walking tours make getting around simple while special programs at ten historic sites throughout the community provide something for every size and taste.
		
		Learn the inside stories of some of Philadelphia&acute;s most important colonial landmarks: put your hand to colonial paper-making techniques at Historic Rittenhouse Town; try out some early American toys at Upsala; and “meet” British General Howe at the Deshler-Morris House, his one-time war headquarters. The historic re-enactment of the 1777 Battle of Germantown takes place at Cliveden.
		
		In addition to Rittenhouse Town, Upsala, the Deschler-Morris House and Clivedon, you&acute;ll visit the Concord School and Upper Burying Ground, where solider and officers are buried; Grumblethorpe, site of one of the battles legendary death scenes; the Johnson House, which showcase the role of African-Americans in the Revolutionary War; and two of the cities most famous colonial houses, Stenton and Wyck.
		<h3>Come Prepared </h3>
		
		There is fee for entry and parking may be limited. It is recommended that visitors consider taking public transportation to Germantown Avenue for the festivities.
		<h3>Don&acute;t Miss </h3>
		
		The battle reenactments at Cliveden are absolute must-sees.
		<h3>Outsider&acute;s Tip</h3>
		
		Make the most of Revolutionary Germantown Festival by purchasing a Passport that covers the cost of admission to all participating sites for the day. The Passport contains a list of the timed events throughout the day along with a map for self guided walking tours of the Germantown area. Passports can be pre-ordered or purchased the day of the event. An individual pass is $15 and the family pass is $25.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Market'),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@revolutionarygermantown.com',
							"geodir_website"		=> 'http://www.revolutionarygermantown.org/',
							"geodir_twitter"		=> 'http://twitter.com/revolutionarygermantown',
							"geodir_facebook"		=> 'http://facebook.com/revolutionarygermantown',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+10 days")),
							"starttime"					=> '10:00 AM',
							"endtime"						=> '12:00 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(11):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival22.jpg";
		$image_array[] = "$dummy_image_url/festival19.jpg";
		$image_array[] = "$dummy_image_url/festival20.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Strawberry Festival at Peddler Village',
							"post_desc"	=>	'
		<h3>The Experience </h3>
		
		Celebrate spring at Peddler&acute;s Village&acute;s celebrated Strawberry Festival, where festive foods, children&acute;s activities, pie-eating contests and a lively lineup of family entertainment are just some of the weekend&acute;s exciting attractions.
		
		Culinary highlights include strawberries served fresh and unadorned, dipped in chocolate and deep-fried in fritters or simply in shortcake, assorted pastries and fruit smoothies. More than 30 craftspeople will exhibit and sell their original handcrafted works.
		
		Artisans show their wares and demonstrate their skills at the Street Road Green Artisan Area, while live entertainment and pie-eating contests add to the festivities of this traditional Spring celebration.
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array(''),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@peddlersvillage.com',
							"geodir_website"		=> 'http://www.peddlersvillage.com/',
							"geodir_twitter"		=> 'http://twitter.com/peddlersvillage',
							"geodir_facebook"		=> 'http://facebook.com/peddlersvillage',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+14 days")),
							"starttime"					=> '9:00 AM',
							"endtime"						=> '4:00 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(12):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival23.jpg";
		$image_array[] = "$dummy_image_url/festival24.jpg";
		$image_array[] = "$dummy_image_url/festival25.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Taste of Philadelphia',
							"post_desc"	=>	'
		A favorite of foodies, Taste of Philadelphia - the official kick-off to Wawa Welcome America! - returns for the fifth year, with more restaurants than ever joining the gastronomical festivities. Some of the city&acute;s most popular eateries serve up their specialties, and entertainment by Morris Day and the Time & to the festive atmosphere.
		
		Admission to Penn&acute;s Landing is free and the “tastes” from participating restaurants are just a few dollars - a fraction of regular entrée-sized prices.
		Dates & Times
		
		Friday, June 25
		5 p.m.
		
		Interested in sampling cuisine from the region&acute;s best chefs? Check out the opening of three days of Taste of Philadelphia. Come and try amazing dishes from some of the most popular restaurants in the city and find new favorite menu items! Stroll the waterfront while you&acute;re “dining” and listen to local musical performers.
		
		Saturday, June 26
		11 a.m.
		
		Taste of Philadelphia continues with its first full day of music, food and fun! Sample some of the best cuisine in the city. There&acute;s music all day to enjoy and help you keep your appetite up. Stay for the evening concert (8 p.m.)starring Morris Day & The Time, followed by the first of three fireworks displays during Wawa Welcome America!.
		
		Sunday, June 27
		11 a.m.
		
		Don&acute;t miss the final day to sample delicious bites from Philadelphia&acute;s many restaurants. There&acute;s more music and fun to be had, so bring your family and friends!
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array('Taste'),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@welcomeamerica.com',
							"geodir_website"		=> 'http://www.welcomeamerica.com/',
							"geodir_twitter"		=> 'http://twitter.com/welcomeamerica',
							"geodir_facebook"		=> 'http://facebook.com/welcomeamerica',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+14 days")),
							"starttime"					=> '10:00 AM',
							"endtime"						=> '12:00 PM',
							"post_dummy" 	=> '1'
							);
	break;
	
	case(13):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival26.jpg";
		$image_array[] = "$dummy_image_url/festival24.jpg";
		$image_array[] = "$dummy_image_url/festival25.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Riverfront Ramble',
							"post_desc"	=>	'
		<h3>The Experience </h3>
		
		The Riverfront Ramble, a massive, 14-mile-long party and festival celebrating Delaware County&acute;s waterfront communities, caps off your summer with a day of fun.
		
		Towns from Marcus Hook to Tinicum Township roll out the welcome mat for visitors with a September festival of food, crafts, music, boats, contests, fireworks and much more.
		
		Nibbling is easy along the entire river route, but it&acute;s in Marcus Hook&acute;s Market Square Memorial Park where more than 20 restaurants and caterers put on a serious festival of food.
		
		There will be free concerts, hot air balloon rides, craft shows, boating and family-friendly activities to enjoy all weekend long. Don&acute;t miss it!
		
		Enjoy a family day on the Delaware River that mingles old memories with beautiful new recreational areas. Boating is encouraged, whether it&acute;s under sail, paddle or motor. Boat shows, tall ships, car shows and sports clinics are included, as is a wildlife tour at the John Heinz Wildlife Refuge in Tinicum Township - fun for birdwatchers, butterfly watchers, or anyone with a camera.
		
		Through it all, you can witness the changing face of Brandywine&acute;s waterfront, a region rich in history and brimming with the development of beautiful new parks. The 14-mile trail will be included in the coastal zone bike trail, which will allow bikers to pedal the entire eastern coastline.
		Come Prepared
		
		Bring a sweater, blanket or beach chair for concerts and fireworks, which are spread out over three towns. There will be food and beverage vendors at all locations.
		<h3>Don&acute;t Miss </h3>
		
		
		Three riverfront fireworks displays at 8:45, for a full, 14-mile display
		<h3>Outsider  Tip</h3>
		
		The Ramble is capped off each year with a string of evening concerts from three separate locations along the river, Barry Bridge Park in Chester, Market Square Memorial Park in Marcus Hook and Governor Printz Park in Tinicum. The concerts are followed by dazzling fireworks displays launched from all three locations!
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array(''),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@riverfrontramble.com',
							"geodir_website"		=> 'http://www.riverfrontramble.org/',
							"geodir_twitter"		=> 'http://twitter.com/riverfrontramble',
							"geodir_facebook"		=> 'http://facebook.com/riverfrontramble',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+15 days")),
							"starttime"					=> '10:00 AM',
							"endtime"						=> '12:00 PM',
							"post_dummy" 	=> '1'
							);
	break;

	case(13):
		$image_array = array();
		$post_meta = array();
		$image_array[] = "$dummy_image_url/festival27.jpg";
		$image_array[] = "$dummy_image_url/festival24.jpg";
		$image_array[] = "$dummy_image_url/festival25.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival5.jpg";
		$image_array[] = "$dummy_image_url/festival7.jpg";
		$image_array[] = "$dummy_image_url/festival8.jpg";
		$image_array[] = "$dummy_image_url/festival9.jpg";
		$image_array[] = "$dummy_image_url/festival1.jpg";
		$image_array[] = "$dummy_image_url/festival2.jpg";
		
		$post_info[] = array(
							"listing_type"	=> 'gd_event',
							"post_title"	=>	'Philadelphia Folk Festival',
							"post_desc"	=>	'
		The hills of Schwenksville, Pennsylvania come alive every summer when music legends like Arlo Guthrie, Pete Seeger and Richie Havens share the sunlight at this festival of folk music and dance in the green valley of Schwenksville&acute;s Old Poole Farm.
		
		This year&acute;s 48th annual festival features such notable acts as Adrien Reju, the Del McCoury Band and Iron and Wine, among others. All told, the festival features more than 75 hours of great folk music and more than 60 talented musicians.
		
		Join thousands of people sprawled out on the hillside as you sing along, clap or just enjoy the music that fills the pastoral landscape. Five stages operate simultaneously, and daytime showcase concerts feature an array of exciting new performers.
		
		Music is everywhere - from late-night singalongs, to bonfires in the festival campgrounds, to parking lot pickers having their own impromptu jam sessions. Don&acute;t miss it!
		<h3> History </h3>
		
		The festival, which is produced and run by volunteers and sponsored by the non-profit Philadelphia Folksong Society, has been bringing world-class folk music to the area for nearly 50 years, and many music fans plan their vacations around the event.
		
		The Philadelphia Folksong Society, the premiere folk organization in the greater Philadelphia region, is known nationally and internationally for producing the famous festival. It offers a wealth of member benefits, including Free House Concerts and Workshops and Sings as well as discounts to many other events.
		<h3>COME PREPARED </h3>
		
		Tickets range in price according to length of stay at the event, and you get a discount if you buy them in advance.
		<h3>DON&acute;t MISS </h3>
		
		A mind-boggling craft show, which offer demonstrations as well as merchandise. And if you&acute;re up for a fun weekend of camping, check out the special free concert in the campground Thursday night, which is only open to Festival Camping ticket holders.
		Outsider Tip
		
		Prominent artists like Bob Dylan, Tommy Smothers and Bonnie Raitt have shown up unannounced at the festival so look out for familiar stars. 
		',
							
							"post_images"	=>	$image_array,
							"post_category"	=>	array('gd_eventcategory'  => array('Events')),
							"post_tags"		=>	array(''),
							"video"			=> '',
							"geodir_contact"		=> '(215) 922-2386 ',
							"geodir_email"			=> 'info@folkfest.com',
							"geodir_website"		=> 'http://www.folkfest.org/',
							"geodir_twitter"		=> 'http://twitter.com/folkfest',
							"geodir_facebook"		=> 'http://facebook.com/folkfest',
							"event_recurring_dates"	=> date_i18n('Y-m-d', strtotime("+30 days")),
							"starttime"					=> '10:00 AM',
							"endtime"						=> '12:50 PM',
							"post_dummy" 	=> '1'
							);
	break;

}

if(!empty($post_info)){
	foreach($post_info as $post_info){
		$default_location = geodir_get_default_location();
		if($city_bound_lat1>$city_bound_lat2)	
			$dummy_latitude  = geodir_random_float(geodir_random_float($city_bound_lat1, $city_bound_lat2), geodir_random_float($city_bound_lat2, $city_bound_lat1));
		else
			$dummy_latitude  = geodir_random_float(geodir_random_float($city_bound_lat2,$city_bound_lat1),geodir_random_float($city_bound_lat1,$city_bound_lat2));	
			
	
		if($city_bound_lng1>$city_bound_lng2)
			$dummy_longitude = geodir_random_float(geodir_random_float($city_bound_lng1,$city_bound_lng2),geodir_random_float($city_bound_lng2,$city_bound_lng1));
		else
			$dummy_longitude = geodir_random_float(geodir_random_float($city_bound_lng2,$city_bound_lng1),geodir_random_float($city_bound_lng1,$city_bound_lng2));
		$addresses=array() ;
		$postal_code='';
		$address='';
		
		$addresses = geodir_get_address_by_lat_lan($dummy_latitude,$dummy_longitude)	;
		
		
		if(!empty($addresses))
		{
			foreach($addresses as $add_key=>$add_value)
			{
				if($add_value->types[0]=='postal_code')
				{
					$postal_code = $add_value->long_name;
				}
				
				if($add_value->types[0]=='street_number')
				{
					if($address!='')
						$address.= ','.$add_value->long_name;
					else
						$address.= $add_value->long_name;	
				}
				if($add_value->types[0]=='route')
				{
					if($address!='')
						$address.= ','.$add_value->long_name;
					else
						$address.= $add_value->long_name;	
				}
				if($add_value->types[0]=='neighborhood')
				{
					if($address!='')
						$address.= ','.$add_value->long_name;
					else
						$address.= $add_value->long_name;	
				}
				if($add_value->types[0]=='sublocality')
				{
					if($address!='')
						$address.= ','.$add_value->long_name;
					else
						$address.= $add_value->long_name;	
				}
				
			}
			
			$post_info['street'] =$address;
			$post_info['city'] = $default_location->city;
			$post_info['region'] = $default_location->region;
			$post_info['country'] = $default_location->country;	
			$post_info['zip'] = $postal_code;
			$post_info['latitude'] = $dummy_latitude;
			$post_info['longitude'] = $dummy_longitude;
			
		}
		geodir_save_listing($post_info, true);
		
	}
}
