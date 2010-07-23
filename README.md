# Field General Addon for ExpressionEngine 1.x

Field General lets you assign multiple field groups to any weblog/channel. This lets you reuse and share sets of fields, rather than having to duplicate them for each channel. [Gypsy][1] has been a popular solution to this problem, but in my experience can become difficult to manage and control the order of your fields. With Field General, you an also **share field groups across multiple MSM sites**!

## Usage

I use Field General to break my fields into logical field groups that will be reused between channels (e.g. *SEO/Meta Fields*, *Page Body Fields*). In certain instances, you may want to create a field group to contain just a single field such as *Alternate Title* or *Page Body*. Field General lets you assign as many field groups to a channel as you like, so go for it.

## Credit

Thanks to [@kswedberg's][2] for rockin' the drag 'n drop!

## EE2?

While the publish form in EE2 is much more easily customized, the same problem exists in that you can only select from fields in the single field group assigned to that channel. Enter Field General...except the hook we use `publish_form_field_query` doesn't exist in EE2 as of yet. So it seems I'll need to rework things a bit. Looks like it should [still be possible][3], however...

[1]: http://devot-ee.com/add-ons/gypsy/  "Gypsy"
[2]: http://learningjquery.com         "Karl Swedberg"
[3]: http://expressionengine.com/forums/viewthread/160740#g9