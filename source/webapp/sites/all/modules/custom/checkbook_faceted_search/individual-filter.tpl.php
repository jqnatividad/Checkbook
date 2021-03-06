<?php
/**
* This file is part of the Checkbook NYC financial transparency software.
* 
* Copyright (C) 2012, 2013 New York City
* 
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as
* published by the Free Software Foundation, either version 3 of the
* License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
* 
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jrobertson
 * Date: 2/27/13
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 * $node
 * $name
 */

//Facets that have Url parameters that match the current Url will be disabled from de-selecting by default.
//To enable the user to de-select the default criteria (for advanced search), set "allowFacetDeselect":true in the config
$disableFacet = !(isset($node->widgetConfig->allowFacetDeselect) ? $node->widgetConfig->allowFacetDeselect : false);
if($disableFacet) { //only URL parameters count and can be disabled
    $query_string = $_GET['q'];
    $is_new_window = preg_match('/newwindow/i',$query_string);
    $url_ref = $is_new_window ? $_GET['q'] : $_SERVER['HTTP_REFERER'];
    $disableFacet = preg_match('"/'.$node->widgetConfig->urlParameterName.'/"',$url_ref);
}

if(isset($node->widgetConfig->maxSelect) && !$disableFacet){
  $tooltip = 'title="Select upto ' . $node->widgetConfig->maxSelect . '"';
}
else{
$tooltip = "";
}

//Amount Filter
if($node->widgetConfig->filterName == 'Amount') {
    $showAllRecords = isset($node->widgetConfig->showAllRecords) ? $node->widgetConfig->showAllRecords : false;
    if(!$showAllRecords) {
        $params = explode('~', _getRequestParamValue($node->widgetConfig->urlParameterName));
        if($params[0]) {
            $unchecked = null;
        }
    }
}

//Payroll Range Filter
$is_payroll_range_filter =
    ($node->widgetConfig->filterName == 'Gross Pay YTD') ||
    ($node->widgetConfig->filterName == 'Annual Salary') ||
    ($node->widgetConfig->filterName == 'Overtime Payment');
if($is_payroll_range_filter) {
    $showAllRecords = isset($node->widgetConfig->showAllRecords) ? $node->widgetConfig->showAllRecords : false;
    if(!$showAllRecords) {
        $params = explode('~', _getRequestParamValue($node->widgetConfig->urlParameterName));
        if($params[0]) {
            $unchecked = null;
        }
    }
}

//Payroll Type Filter
$count = 0;
if($node->widgetConfig->filterName == 'Payroll Type') {

    switch($node->nid) {
        case 898:
        case 899:
        //Advanced Search Payroll Type Facets
        foreach($checked as $key => $value) {
            if($value[0] == 2 || $value[0] == 3) {
                $count = $count + $value[2];
                $id = "2~3";
                unset($checked[$key]);
            }
            else {
                array_push($checked,array($value[0],PayrollType::$SALARIED,$value[2]));
                unset($checked[$key]);
            }
        }
        if($count > 0) {
            array_push($checked,array($id,PayrollType::$NON_SALARIED,$count));
        }
        break;
    }
}

//Modified Expense Budget Filter
if($node->widgetConfig->filterName == 'Modified Expense Budget') {
    $showAllRecords = isset($node->widgetConfig->showAllRecords) ? $node->widgetConfig->showAllRecords : false;
    if(!$showAllRecords) {
        $params = explode('~', _getRequestParamValue($node->widgetConfig->urlParameterName));
        if($params[0]) {
            $unchecked = null;
        }
    }
}

//Revenue Recognized Filter
if($node->widgetConfig->filterName == 'Revenue Recognized') {
    $showAllRecords = isset($node->widgetConfig->showAllRecords) ? $node->widgetConfig->showAllRecords : false;
    if(!$showAllRecords) {
        $params = explode('~', _getRequestParamValue($node->widgetConfig->urlParameterName));
        if($params[0]) {
            $unchecked = null;
        }
    }
}

//Checking 'Asian-American' filter in MWBE Category Facet
$count =0;
if($node->widgetConfig->filterName == 'M/WBE Category'){
    $dashboard = _getRequestParamValue('dashboard');
    foreach($unchecked as $key => $value){
        if(isset($dashboard) && $dashboard != 'ss'){
            if($value[0] == 7 || $value[0] == 11){
                unset($unchecked[$key]);
            }
        }
    }

    foreach($checked as $key=>$value){
        if($value[0] == 4 || $value[0] == 5){
            $count = $count + $value[2];
            $id = "4~5";
            unset($checked[$key]);
        }else{
            array_push($checked,array($value[0],MappingUtil::getMinorityCategoryById($value[0]),$value[2]));
            unset($checked[$key]);
        }
    }
    if($count > 0 )array_push($checked,array($id,'Asian American',$count));
}

//Data alteration for Vendor Type Facet
if($node->widgetConfig->filterName == 'Vendor Type'){
    $vendor_types = _getRequestParamValue('vendortype');
    $vendor_type_data = MappingUtil::getVendorTypes($checked, $vendor_types);
    $vendor_type_data = MappingUtil::getVendorTypes($unchecked, $vendor_types);
    $checked = $vendor_type_data['checked'];
    $unchecked = $vendor_type_data['unchecked'];
}

if(count($checked) == 0){
    $display_facet ="none";
    $span = "";
}else{
    $display_facet ="block";
    $span = "open";
}

if(strtolower($filter_name) == 'agency'){
    if(_checkbook_check_isEDCPage()){
        $filter_name = 'Other Government Entity';
    }else{
        $filter_name = 'Citywide Agency';
    }
}
if(strtolower($filter_name) == 'vendor'){
    if(_checkbook_check_isEDCPage()){
        $filter_name = 'Prime Vendor';
    }
}
$id_filter_name = str_replace(" ", "_", strtolower($filter_name));
?>
<div class="filter-content <?php if( $hide_filter != "") print "disabled"; ?>"><div <?php print $hide_filter; ?>>
  <div class="filter-title" <?php print $tooltip ?>><span class="<?php print $span;?>">By <?php print $filter_name;?></span></div>
  <div class="facet-content" style="display:<?php echo $display_facet; ?>">
  <div class="progress"></div>
  <?php  
    $pages = ceil($node->totalDataCount/$node->widgetConfig->limit);   
    if((isset($checked) && $node->widgetConfig->maxSelect == count($checked)) || count($checked) + count($unchecked) == 0 || $disableFacet){
      $disabled = " DISABLED='true' " ;
    }else{
      $disabled = "" ;
    }   
    if( !isset($node->widgetConfig->autocomplete) || $node->widgetConfig->autocomplete == true  ){ ?>
  <div class="autocomplete"><input class="autocomplete" <?php print $disabled; ?> pages="<?php print $pages ?>" type="text" name="<?php print $autocomplete_field_name ?>" 
            autocomplete_param_name="<?php print $autocomplete_param_name ?>" nodeid="<?php print $node->nid ;?>" id="<?php print $autocomplete_id ?>"></div>
        <?php } ?>
  <div class="checked-items">
    <?php
    if((isset($checked) && $node->widgetConfig->maxSelect == count($checked)) || count($checked) + count($unchecked) == 0 ){
        $disabled = " DISABLED='true' " ;
    }else{
        $disabled = "" ;
    }
    $disableFacet = $disableFacet ? " DISABLED='true' " : "";

    $ct = 0;
    foreach ($checked as $row) {
        $row[0] = str_replace('__','/', $row[0]);
        $row[1] = str_replace('__','/', $row[1]);
        $id = $id_filter_name."_checked_".$ct;
        echo '<div class="row">';
        echo '<div class="checkbox"><input class="styled" id="'.$id.'" name="' . $autocomplete_id . '" type="checkbox" ' . $disableFacet . 'checked="checked" value="' . urlencode(html_entity_decode($row[0],ENT_QUOTES)) . '" onClick="return applyTableListFilters();"><label for="'.$id.'"></label></div>';
        echo '<div class="name">' . _break_text_custom2($row[1],15) . '</div>';
        echo '<div class="number"><span class="active">' . number_format($row[2]) . '</span></div>';
        echo '</div>';
        $ct++;
    }
    ?>
  </div>
  <div class="options">
    <div class="rows">
    <?php
    $ct = 0;
    foreach ($unchecked as $row) {
        $row[0] = str_replace('__','/', $row[0]);
        $row[1] = str_replace('__','/', $row[1]);
        $id = $id_filter_name."_unchecked_".$ct;
        echo '<div class="row">';
        echo '<div class="checkbox"><input class="styled" id="'.$id.'" name="' . $autocomplete_id . '" type="checkbox" '  .  $disabled .  'value="' . urlencode(html_entity_decode($row[0],ENT_QUOTES)) . '" onClick="return applyTableListFilters();"><label for="'.$id.'"></label></div>';
        echo '<div class="name">' . _break_text_custom2($row[1],15) . '</div>';
        echo '<div class="number"><span>' . number_format($row[2]) . '</span></div>';
        echo '</div>';
        $ct++;
    }
    ?>
    </div>
  </div>
        </div>
  </div>
</div>
<?php
  if($node->widgetConfig->facetPager == true){
    $scroll_facet = "var page" . $node->nid ." = 0;
    $(this).next().find('.options').mCustomScrollbar('destroy');
    $(this).next().find('.options').mCustomScrollbar({
        horizontalScroll:false,
        scrollButtons:{
            enable:false
        },
        callbacks:{
            onTotalScroll: function (){   
				var pages = $(this).next().find('input.autocomplete').attr('pages');
				if(pages == 1) return false;
				if(page" . $node->nid ."  >= pages ) {
					return false;
				}
				page" . $node->nid ."++;                      
                paginateScroll(" . $node->nid .", page" . $node->nid .")
            },
            onTotalScrollBack: function(){
                var pages = $(this).next().find('input.autocomplete').attr('pages');
                if(pages == 1) return false;
                if (page" . $node->nid ." > 0){
                    page" . $node->nid ."--;
                    paginateScroll(" . $node->nid .", page" . $node->nid .");
                }
                
            }
        },
        theme:'dark'
    });";
    
  }elseif($node->widgetConfig->facetNoPager == true){
    $scroll_facet = "
        $(this).next().find('.options').mCustomScrollbar('destroy');
        $(this).next().find('.options').mCustomScrollbar({
            horizontalScroll:false,
            scrollButtons:{
                enable:false
            },
            theme:'dark'
        });
      ";
   }
    $js .= "(function($){
          $('.filter-title').each(function(){
            if($(this).children('span').hasClass('open')){";
    $js .= $scroll_facet . "}});

          $('.filter-title').unbind('click');
          $('.filter-title').click(function(){
                if($(this).next().css('display') == 'block'){
                    $(this).next().css('display','none');
                    $(this).children('span').removeClass('open');
                    $(this).next().find('.options').mCustomScrollbar('destroy');
                } else {
                    $(this).next().css('display','block');
                    $(this).children('span').addClass('open');
                    $(this).next().find('.options').mCustomScrollbar('destroy');
                    $(this).next().find('.options').mCustomScrollbar({
                        horizontalScroll:false,
                        scrollButtons:{
                            enable:false
                        },
                        theme:'dark'
                    });
                }
            });

            })(jQuery);";
  if(isset($js)){
    echo '<script type="text/javascript">' . $js . '</script>';
  }
  if ($node->widgetConfig->addJS1) {
    echo '<script type="text/javascript">' . $node->widgetConfig->addJS . '</script>';
  }
?>