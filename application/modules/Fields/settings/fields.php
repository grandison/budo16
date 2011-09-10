<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: fields.php 8519 2011-02-25 02:21:42Z jung $
 * @author     John
 */
return array(
/*
Nickname
-First Name
-Last Name
-Gender
-Birthday
-About Me
-Websites
-Twitter
-Facebook
-AIM

-Country
-City
-ZIP/Postal Code
Height
-Weight
-Income
-Occupation
-Religion
-Education
-Relationship Status
-Ethnicity
-Interests
Hobbies
Favorite Music
Favorite Movies
Favorite Books

Currency
*/
  'categories' => array(
    'generic' => array(
      'label' => 'Generic',
    ),
    'specific' => array(
      'label' => 'Specific',
    ),
  ),
  'dependents' => array(
    'select',
    'multiselect',
    'multi_checkbox',
    'radio',
    'gender',
    'profile_type',
  ),
  'fields' => array(
    // Generic
    'text' => array(
      'label' => 'Single-line Text Input',
      'category' => 'generic',
      'helper' => 'fieldText',
      'multi' => false,
      'dependents' => false,
    ),
    'textarea' => array(
      'label' => 'Multi-line Text Input',
      'category' => 'generic',
      'helper' => 'fieldText',
      'multi' => false,
      'dependents' => false,
    ),
    'select' => array(
      'label' => 'Select Box',
      'category' => 'generic',
      'helper' => 'fieldSelect',
      'multi' => false,
      'dependents' => true,
    ),
    'radio' => array(
      'label' => 'Radio Buttons',
      'category' => 'generic',
      'helper' => 'fieldSelect',
      'multi' => false,
      'dependents' => true,
    ),
    'checkbox' => array(
      'label' => 'Single Checkbox',
      'category' => 'generic',
      'helper' => 'fieldSelect',
      'multi' => false,
      'dependents' => true,
    ),
    'multiselect' => array(
      'label' => 'Multi Select Box',
      'category' => 'generic',
      'helper' => 'fieldMultiselect',
      'multi' => true,
      'dependents' => true,
    ),
    'multi_checkbox' => array(
      'label' => 'Multi Checkbox',
      'category' => 'generic',
      'helper' => 'fieldMultiselect',
      'multi' => true,
      'dependents' => true,
    ),
    'integer' => array(
      'label' => 'Integer',
      'category' => 'generic',
      'helper' => 'fieldText',
      'multi' => false,
      'dependents' => false,
    ),
    'float' => array(
      'label' => 'Float',
      'category' => 'generic',
      'helper' => 'fieldText',
      'multi' => false,
      'dependents' => false,
    ),
    'date' => array(
      'label' => 'Date',
      'category' => 'generic',
      'helper' => 'fieldDate',
      'multi' => false,
      'dependents' => false,
    ),
    'heading' => array(
      'label' => 'Heading',
      'category' => 'generic',
      'helper' => 'fieldHeading',
      'multi' => false,
      'dependents' => false,
    ),
    // Specific
    'profile_type' => array(
      'base' => 'select',
      'label' => 'Profile Type',
      'category' => false, // Hide this
    ),
    'first_name' => array(
      'base' => 'text',
      'label' => 'First Name',
      'category' => 'specific',
    ),
    'last_name' => array(
      'base' => 'text',
      'label' => 'Last Name',
      'category' => 'specific',
    ),
    'gender' => array(
      'base' => 'select',
      'label' => 'Gender',
      'category' => 'specific',
      'importOptions' => true,
      'multiOptions' => array(
        'Male',
        'Female',
      )
    ),
    'birthdate' => array(
      'base' => 'date',
      'label' => 'Birthdate',
      'category' => 'specific',
      'adminFormClass' => 'Fields_Form_Admin_Field_Birthdate',
      'helper' => 'fieldBirthdate',
    ),
    'about_me' => array(
      'base' => 'textarea',
      'label' => 'About Me',
      'category' => 'specific',
    ),

    // Communications
    'website' => array(
      'base' => 'text',
      'label' => 'Website',
      'helper' => 'fieldWebsite',
      'category' => 'specific',
      'sql' => array(
        'type' => 'varchar',
        'length' => 255,
        'default' => '',
      ),
    ),
    'twitter' => array(
      'base' => 'text',
      'label' => 'Twitter',
      'category' => 'specific',
      'helper' => 'fieldTwitter',
    ),
    'facebook' => array(
      'base' => 'text',
      'label' => 'Facebook',
      'category' => 'specific',
      'helper' => 'fieldFacebook',
    ),
    'aim' => array(
      'base' => 'text',
      'label' => 'AIM',
      'category' => 'specific',
      'helper' => 'fieldAim',
    ),

    // Location
    'city' => array(
      'base' => 'text',
      'label' => 'City',
      'category' => 'specific',
      'helper' => 'fieldText',
    ),
    'country' => array(
      'base' => 'select',
      'label' => 'Country',
      'category' => 'specific',
      'helper' => 'fieldCountry',
    ),
    'zip_code' => array(
      'base' => 'integer',
      'label' => 'Zip Code',
      'category' => 'specific',
      'helper' => 'fieldZipCode',
    ),
    'location' => array(
      'base' => 'text',
      'label' => 'Location',
      'category' => 'specific',
      'helper' => 'fieldLocation',
    ),

    // Dating
    'relationship_status' => array(
      'base' => 'select',
      'label' => 'Relationship Status',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'single' => 'Single',
        'relationship' => 'In a Relationship',
        'engaged' => 'Engaged',
        'married' => 'Married',
        'complicated' => 'It\'s Complicated',
        'open' => 'In an Open Relationship',
        'widow' => 'Widowed',
      )
    ),
    'looking_for' => array(
      'base' => 'multi_checkbox',
      'label' => 'Looking For',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'friendship' => 'Friendship',
        'dating' => 'Dating',
        'relationship' => 'A Relationship',
        'networking' => 'Networking',
      ),
    ),
    'partner_gender' => array(
      'base' => 'multi_checkbox',
      'label' => 'Interested In',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'men' => 'Men',
        'women' => 'Women',
      ),
    ),
    'education_level' => array(
      'base' => 'select',
      'label' => 'Education',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'high_school' => 'High School',
        'some_college' => 'Some College',
        'associates' =>  'Associates Degree',
        'bachelors' => 'Bachelors Degree',
        'graduate' => 'Graduate Degree',
        'phd' => 'PhD / Post Doctoral',
      ),
    ),
    'ethnicity' => array(
      'base' => 'multi_checkbox',
      'label' => 'Ethnicity',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'asian' => 'Asian',
        'black' => 'Black / African descent',
        'hispanic' => 'Latino / Hispanic',
        'pacific' => 'Pacific Islander',
        'white' => 'White / Caucasian',
        'other' => 'Other',
      ),
    ),
    'income' => array(
      'base' => 'select',
      'label' => 'Income',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        '25' => 'Less than $25,000',
        '25_35' => '$25,001 to $35,000',
        '35_50' => '$35,001 to $50,000',
        '50_75' => '$50,001 to $75,000',
        '75_100' => '$75,001 to $100,000',
        '100_150' => '$100,001 to $150,000',
        '150' => '$150,001+',
      ),
    ),
    'occupation' => array(
      'base' => 'select',
      'label' => 'Occupation',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'admn' => 'Administrative / Secretarial',
        'arch' => 'Architecture / Interior design',
        'crea' => 'Artistic / Creative / Performance',
        'educ' => 'Education / Teacher / Professor',
        'mngt' => 'Executive / Management',
        'fash' => 'Fashion / Model / Beauty',
        'fina' => 'Financial / Accounting / Real Estate',
        'labr' => 'Labor / Construction',
        'lawe' => 'Law enforcement / Security / Military',
        'legl' => 'Legal',
        'medi' => 'Medical / Dental / Veterinary / Fitness',
        'nonp' => 'Nonprofit / Volunteer / Activist',
        'poli' => 'Political / Govt / Civil Service / Military',
        'retl' => 'Retail / Food services',
        'retr' => 'Retired',
        'sale' => 'Sales / Marketing',
        'self' => 'Self-Employed / Entrepreneur',
        'stud' => 'Student',
        'tech' => 'Technical / Science / Computers / Engineering',
        'trav' => 'Travel / Hospitality / Transportation',
        'othr' => 'Other profession',
      ),
    ),
    'political_views' => array(
      'base' => 'select',
      'label' => 'Political Views',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'mid' => 'Middle of the Road',
        'far_right' => 'Very Conservative',
        'right' => 'Conservative',
        'left' => 'Liberal',
        'far_left' => 'Very Liberal',
        'anarchy' => 'Non-conformist',
        'libertarian' => 'Libertarian',
        'green' => 'Green',
        'other' => 'Other',
      ),
    ),
    'religion' => array(
      'base' => 'select',
      'label' => 'Religion',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'agnostic' => 'Agnostic',
        'atheist' => 'Atheist',
        'buddhist' => 'Buddhist',
        'taoist' => 'Taoist',
        'catholic' => 'Christian (Catholic)',
        'mormon' => 'Christian (LDS)',
        'protestant' => 'Christian (Protestant)',
        'hindu' => 'Hindu',
        'jewish' => 'Jewish',
        'muslim' => 'Muslim',
        'spiritual' => 'Spiritual',
        'other' => 'Other',
      ),
    ),
    'weight' => array(
      'base' => 'select',
      'label' => 'Weight',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'slender' => 'Slender',
        'average' => 'Average',
        'athletic' => 'Athletic',
        'heavy' => 'Heavy',
        'stocky' => 'Stocky',
        'little_fat' => 'A few extra pounds',
      ),
    ),
    'zodiac' => array(
      'base' => 'select',
      'label' => 'Zodiac',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'capricorn' => 'Capricorn',
        'aquarius' => 'Aquarius',
        'pisces' => 'Pisces',
        'aries' => 'Aries',
        'taurus' => 'Taurus',
        'gemini' => 'Gemini',
        'cancer' => 'Cancer',
        'leo' => 'Leo',
        'virgo' => 'Virgo',
        'libra' => 'Libra',
        'scorpio' => 'Scorpio',
        'sagittarius' => 'Sagittarius',
      ),
    ),
    'eye_color' => array(
      'base' => 'select',
      'label' => 'Eye Color',
      'helper' => 'fieldOptions',
      'category' => 'specific',
      'multiOptions' => array(
        'black' => 'Black',
        'blue' => 'Blue',
        'brown' => 'Brown',
        'grey' => 'Grey',
        'green' => 'Green',
        'hazel' => 'Hazel',
      ),
    ),

    'interests' => array(
      'base' => 'textarea',
      'label' => 'Interests',
      'category' => 'specific',
      'helper' => 'fieldTextareaLinked',
    ),

    'currency' => array(
      'base' => 'float',
      'label' => 'Currency',
      'category' => 'specific',
      'helper' => 'fieldCurrency',
      'adminFormClass' => 'Fields_Form_Admin_Field_Currency',
    )
  ),
) ?>