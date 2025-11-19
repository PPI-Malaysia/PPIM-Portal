# AJAX Search Components

Reusable AJAX search functionality for University and Postcode dropdowns using Choices.js.

## Files

- `ajax-university.js` - University search functionality
- `ajax-postcode.js` - Postcode search functionality

## Requirements

- Choices.js library (must be loaded before these scripts)
- API endpoints:
  - `/assets/php/API/get-university.php`
  - `/assets/php/API/get-postcode.php`

## Installation

### 1. Include Required Scripts

Add these scripts to your HTML page (after Choices.js):

```html
<!-- Choices.js (required) -->
<script src="../assets/js/vendor.min.js"></script>

<!-- AJAX Search Scripts -->
<script src="../assets/js/ajax-university.js"></script>
<script src="../assets/js/ajax-postcode.js"></script>
```

### 2. Add Select Elements

Add select elements to your form with unique IDs:

```html
<select id="university-select" name="university_id">
    <option value="">Select University</option>
</select>

<select id="postcode-select" name="postcode_id">
    <option value="">Select Postcode</option>
</select>
```

### 3. Initialize the Search

Initialize the search functionality when the page loads:

```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize university search
    const universitySearch = initUniversitySearch('university-select', {
        apiUrl: '../assets/php/API/get-university.php',
        limit: 100,
        searchMinLength: 2,
        debug: true
    });

    // Initialize postcode search
    const postcodeSearch = initPostcodeSearch('postcode-select', {
        apiUrl: '../assets/php/API/get-postcode.php',
        limit: 100,
        searchMinLength: 2,
        debug: true
    });
});
</script>
```

## Configuration Options

### University Search Options

```javascript
initUniversitySearch(selectId, {
    apiUrl: '../assets/php/API/get-university.php',  // API endpoint
    limit: 100,                                       // Initial load limit
    searchMinLength: 2,                              // Min chars to trigger search
    placeholder: 'Type to search university...',     // Placeholder text
    noResultsText: 'No universities found',          // No results message
    noChoicesText: 'Type to search...',              // Empty state message
    onLoad: function(data, searchTerm) {             // Callback after data loads
        console.log('Loaded universities:', data);
    },
    onError: function(error) {                       // Callback on error
        console.error('Error:', error);
    },
    debug: true                                      // Enable console logging
});
```

### Postcode Search Options

```javascript
initPostcodeSearch(selectId, {
    apiUrl: '../assets/php/API/get-postcode.php',    // API endpoint
    limit: 100,                                       // Initial load limit
    searchMinLength: 2,                              // Min chars to trigger search
    placeholder: 'Type to search postcode...',       // Placeholder text
    noResultsText: 'No postcodes found',             // No results message
    noChoicesText: 'Type to search...',              // Empty state message
    filterByCity: 'Kuala Lumpur',                    // Optional city filter
    filterByState: 'Selangor',                       // Optional state filter
    onLoad: function(data, searchTerm) {             // Callback after data loads
        console.log('Loaded postcodes:', data);
    },
    onError: function(error) {                       // Callback on error
        console.error('Error:', error);
    },
    debug: true                                      // Enable console logging
});
```

## API Methods

Both functions return an object with utility methods:

### University Search Methods

```javascript
const universitySearch = initUniversitySearch('university-select', options);

// Access the Choices.js instance
universitySearch.instance;

// Reload data (clears cache)
universitySearch.reload();

// Search for specific term
universitySearch.search('Kuala Lumpur');

// Clear cache
universitySearch.clearCache();

// Set selected value programmatically
universitySearch.setValue('123');

// Get current selected value
const value = universitySearch.getValue();
```

### Postcode Search Methods

```javascript
const postcodeSearch = initPostcodeSearch('postcode-select', options);

// Access the Choices.js instance
postcodeSearch.instance;

// Reload data (clears cache)
postcodeSearch.reload();

// Search for specific term
postcodeSearch.search('50603');

// Filter by city
postcodeSearch.filterByCity('Kuala Lumpur');

// Filter by state
postcodeSearch.filterByState('Selangor');

// Clear all filters
postcodeSearch.clearFilters();

// Clear cache
postcodeSearch.clearCache();

// Set selected value programmatically
postcodeSearch.setValue('50603');

// Get current selected value
const value = postcodeSearch.getValue();
```

## Examples

### Basic Usage

```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/vendor.min.css">
</head>
<body>
    <form>
        <select id="university-select" name="university_id">
            <option value="">Select University</option>
        </select>

        <select id="postcode-select" name="postcode_id">
            <option value="">Select Postcode</option>
        </select>

        <button type="submit">Submit</button>
    </form>

    <script src="../assets/js/vendor.min.js"></script>
    <script src="../assets/js/ajax-university.js"></script>
    <script src="../assets/js/ajax-postcode.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initUniversitySearch('university-select');
        initPostcodeSearch('postcode-select');
    });
    </script>
</body>
</html>
```

### Advanced Usage with Callbacks

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize university search with callbacks
    const universitySearch = initUniversitySearch('university-select', {
        limit: 50,
        debug: true,
        onLoad: function(data, searchTerm) {
            console.log(`Loaded ${data.length} universities`);

            // Do something after data loads
            if (data.length === 0) {
                alert('No universities found!');
            }
        },
        onError: function(error) {
            console.error('Failed to load universities:', error);
            alert('Error loading universities. Please try again.');
        }
    });

    // Initialize postcode search with state filter
    const postcodeSearch = initPostcodeSearch('postcode-select', {
        filterByState: 'Selangor',
        debug: true,
        onLoad: function(data, searchTerm) {
            console.log(`Loaded ${data.length} postcodes in Selangor`);
        }
    });

    // Set initial values programmatically
    setTimeout(function() {
        universitySearch.setValue('123'); // Set university ID
        postcodeSearch.setValue('50603'); // Set postcode
    }, 1000);
});
```

### Multiple Dropdowns on Same Page

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Primary university selector
    const primaryUniversity = initUniversitySearch('university-select-1', {
        placeholder: 'Select Primary University',
        debug: false
    });

    // Secondary university selector
    const secondaryUniversity = initUniversitySearch('university-select-2', {
        placeholder: 'Select Secondary University',
        debug: false
    });

    // Home postcode
    const homePostcode = initPostcodeSearch('home-postcode', {
        placeholder: 'Home Postcode',
        debug: false
    });

    // Work postcode
    const workPostcode = initPostcodeSearch('work-postcode', {
        placeholder: 'Work Postcode',
        debug: false
    });
});
```

## Troubleshooting

### Issue: "Choices.js library not loaded"

**Solution:** Make sure Choices.js is included before the AJAX search scripts:

```html
<script src="../assets/js/vendor.min.js"></script> <!-- Must come first -->
<script src="../assets/js/ajax-university.js"></script>
<script src="../assets/js/ajax-postcode.js"></script>
```

### Issue: "Select element not found"

**Solution:** Make sure:
1. The select element exists in the HTML
2. The ID matches exactly
3. You're calling init functions after DOM is loaded

### Issue: No data loads

**Solution:** Check:
1. API URLs are correct
2. User is authenticated
3. Browser console for errors
4. Network tab for failed requests

### Issue: Search is slow

**Solution:**
1. Reduce the `limit` option
2. Increase `searchMinLength` to 3 or 4
3. Check database indexes on search fields

## Performance Tips

1. **Use caching**: Both components cache results automatically
2. **Set reasonable limits**: Don't load more than 100-200 items initially
3. **Increase searchMinLength**: Set to 3-4 characters for large datasets
4. **Disable debug mode**: Set `debug: false` in production

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## License

Internal use only - PPIM Portal project
