# Event Espresso Venue Address - Replace `<br>` with Commas

This customization changes the Event Espresso venue address display so the address appears in a single row with commas instead of `<br />` line breaks.

## Result

![Venue address displayed in a single row](https://github.com/developer1wiseryk/screenshots/blob/main/venue-address-single-row-result.png?raw=true)

## Dependencies

- Event Espresso
- WordPress child theme

## File to Customize

Copy the following Event Espresso template file into your child theme:

```text
espresso-events-table-template.template.php
```

The customized copy should be kept inside the child theme.

## Code

Add this code where the venue address is output inside `espresso-events-table-template.template.php`:

```php
// Code to Show the address in single row
ob_start();
espresso_venue_address();
$faCalgary_venue_address = ob_get_clean();
$faCalgary_venue_address = str_replace('<br />', ', ', $faCalgary_venue_address);
echo $faCalgary_venue_address;
// Code End
```

The code captures the output from `espresso_venue_address()`, replaces `<br />` with `, `, and then displays the updated address.

## Code Placement

The code is added in the venue section of the copied Event Espresso table template, as shown below.

![Code placement in Event Espresso template](https://github.com/developer1wiseryk/screenshots/blob/main/template-code-location.png?raw=true)

## How to Use

1. Make sure Event Espresso is installed and your site uses a child theme.
2. Copy `espresso-events-table-template.template.php` from Event Espresso into the child theme.
3. Open the copied template file.
4. Find the section where `espresso_venue_address()` outputs the venue address.
5. Add the code shown above.
6. Save the file and check the Event Espresso events table on the front end.

## Repository Structure

```text
├── child-theme/
│   └── espresso-events-table-template.template.php  # add your real customized 
```

> The screenshots in this README are loaded using direct links from the separate `developer1wiseryk/screenshots` GitHub repository.

> The file in `snippets/` is only a reference copy of the custom code. Add your real customized `espresso-events-table-template.template.php` file to the `child-theme/` folder before pushing the repository.
