# Field General Addon for ExpressionEngine 1.x
Field General lets you assign multiple field groups to any weblog/channel. This lets you reuse and share sets of fields, rather than having to duplicate them for each channel. [Gypsy][1] has been a popular solution to this problem, but in my experience can become difficult to manage and control the order of your fields. With Field General, you an also share field groups across multiple MSM sites†.

## Usage
I use Field General to break my fields into logical field groups that will be reused between channels (e.g. *SEO/Meta Fields*, *Page Body Fields*). In certain instances, you may want to create a field group to contain just a single field such as *Alternate Title* or *Page Body*. Field General lets you assign as many field groups to a channel as you like, so go for it.

## EE2?
While the publish form in EE2 is much more easily customized, the same problem exists in that you can only select from fields in the single field group assigned to that channel. Enter Field General...except the hook we use `publish_form_field_query` doesn't exist in EE2 as of yet. So it seems I'll need to rework things a bit. Looks like it should [still be possible][3], however...Field General is far from a perfect solution, and we with any luck we may have [better solutions on the horizon][4].

[1]: http://devot-ee.com/add-ons/gypsy/  "Gypsy"
[2]: http://github.com/kswedberg         "Karl Swedberg"
[3]: http://expressionengine.com/forums/viewthread/160740#g9
[4]: http://brandon-kelly.com/blog/custom-fields

## † Using Fields across MSM sites
While Field General has no problem assigning custom weblog fields and displaying them across MSM sites in the control panel, unfortunately the weblog module has a bit of trouble with this. While its query gets all fields from all sites, when it parses the custom field tags, it only parses those from the site matching that of the entry it is displaying. Until I can find a better solution, there is a small hack to `mod.weblog.php` that will allow this.

Around line `451` in `mod.weblog.php` *(ExpressionEngine 1.6.9)*, look for:

      /** ----------------------------------------
      /**  parse custom weblog fields
      /** ----------------------------------------*/

and immediately after add:

      // This is so we can share fields between sites (via Field General)
      // Start of hack
      foreach ($this->cfields as $site_id => $cfields) {
        foreach ($cfields as $field_name => $field_id) {
          if ($field_name == $val) {
            $row['site_id'] = $site_id;
          }
        }
      }
      // end of hack