/**
 * AJAX Postcode Search for Choices.js
 * Provides dynamic search functionality for postcode dropdowns
 *
 * Usage:
 * 1. Include this script after Choices.js library
 * 2. Add a select element with a specific ID
 * 3. Call initPostcodeSearch(selectId, options)
 *
 * Example:
 * <select id="postcode-select" name="postcode_id">
 *     <option value="">Select Postcode</option>
 * </select>
 *
 * <script>
 * initPostcodeSearch('postcode-select', {
 *     apiUrl: '../assets/php/API/get-postcode.php',
 *     limit: 100,
 *     searchMinLength: 2
 * });
 * </script>
 */

(function(window) {
    'use strict';

    /**
     * Initialize postcode search on a select element
     * @param {string} selectId - The ID of the select element
     * @param {Object} options - Configuration options
     * @returns {Object} - Returns the Choices instance and utility methods
     */
    window.initPostcodeSearch = function(selectId, options) {
        // Default options
        const defaults = {
            apiUrl: '../assets/php/API/get-postcode.php',
            limit: 100,
            searchMinLength: 2,
            placeholder: 'Type to search postcode or city...',
            noResultsText: 'No postcodes found',
            noChoicesText: 'Type to search...',
            filterByCity: null,
            filterByState: null,
            onLoad: null,
            onError: null,
            debug: false
        };

        // Merge options with defaults
        const config = Object.assign({}, defaults, options);

        // Get the select element
        const selectElement = document.getElementById(selectId);
        if (!selectElement) {
            console.error(`Postcode select element with ID "${selectId}" not found`);
            return null;
        }

        // Check if Choices.js is available
        if (typeof Choices === 'undefined') {
            console.error('Choices.js library not loaded. Please include Choices.js before this script.');
            return null;
        }

        let choicesInstance = null;
        let cache = {};

        try {
            // Initialize Choices.js
            choicesInstance = new Choices(selectElement, {
                searchEnabled: true,
                searchPlaceholderValue: config.placeholder,
                noResultsText: config.noResultsText,
                noChoicesText: config.noChoicesText,
                itemSelectText: 'Click to select',
                loadingText: 'Loading...',
                searchResultLimit: 100,
                shouldSort: false,
                removeItemButton: false,
                searchFloor: config.searchMinLength,
                searchChoices: true
            });

            // Load initial postcodes
            loadPostcodes('');

            // Search on input
            selectElement.addEventListener('search', function(event) {
                const searchTerm = event.detail.value;
                if (searchTerm.length >= config.searchMinLength) {
                    loadPostcodes(searchTerm);
                }
            });

            if (config.debug) {
                console.log(`✓ Postcode search initialized on #${selectId}`);
            }

        } catch (error) {
            console.error('Error initializing postcode Choices:', error);
            if (config.onError) config.onError(error);
            return null;
        }

        /**
         * Load postcodes from API
         * @param {string} searchTerm - Search query
         */
        function loadPostcodes(searchTerm = '') {
            const cacheKey = searchTerm.toLowerCase() +
                            '_' + (config.filterByCity || '') +
                            '_' + (config.filterByState || '');

            // Check cache first
            if (cache[cacheKey]) {
                updateChoices(cache[cacheKey]);
                return;
            }

            // Build URL
            let url = config.apiUrl + '?limit=' + config.limit;

            if (searchTerm) {
                url += '&search=' + encodeURIComponent(searchTerm);
            }

            if (config.filterByCity) {
                url += '&city=' + encodeURIComponent(config.filterByCity);
            }

            if (config.filterByState) {
                url += '&state=' + encodeURIComponent(config.filterByState);
            }

            // Fetch data
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        if (config.debug) {
                            console.log(`✓ Loaded ${data.count} postcodes` + (searchTerm ? ` for "${searchTerm}"` : ''));
                        }

                        // Cache the result
                        cache[cacheKey] = data.data;
                        updateChoices(data.data);

                        // Call onLoad callback
                        if (config.onLoad) {
                            config.onLoad(data.data, searchTerm);
                        }
                    } else {
                        console.error('Failed to load postcodes:', data.error);
                        if (config.onError) config.onError(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching postcodes:', error);
                    if (config.onError) config.onError(error);
                });
        }

        /**
         * Update Choices.js dropdown with new data
         * @param {Array} postcodes - Array of postcode objects
         */
        function updateChoices(postcodes) {
            if (!choicesInstance) {
                console.error('Choices instance not initialized');
                return;
            }

            try {
                // Clear existing choices
                choicesInstance.clearChoices();

                // Build choices array
                const choices = [
                    {
                        value: '',
                        label: 'Select Postcode',
                        placeholder: true,
                        disabled: false
                    }
                ];

                // Add postcode choices
                postcodes.forEach(postcode => {
                    choices.push({
                        value: postcode.zip_code,
                        label: postcode.zip_code + ' - ' + postcode.city + ', ' + postcode.state_name,
                        selected: false,
                        disabled: false,
                        customProperties: {
                            city: postcode.city,
                            state: postcode.state_name
                        }
                    });
                });

                choicesInstance.setChoices(choices, 'value', 'label', true);
            } catch (error) {
                console.error('Error updating postcode choices:', error);
                if (config.onError) config.onError(error);
            }
        }

        // Return public API
        return {
            instance: choicesInstance,
            reload: function() {
                cache = {};
                loadPostcodes('');
            },
            search: function(term) {
                loadPostcodes(term);
            },
            clearCache: function() {
                cache = {};
            },
            filterByCity: function(city) {
                config.filterByCity = city;
                cache = {};
                loadPostcodes('');
            },
            filterByState: function(state) {
                config.filterByState = state;
                cache = {};
                loadPostcodes('');
            },
            clearFilters: function() {
                config.filterByCity = null;
                config.filterByState = null;
                cache = {};
                loadPostcodes('');
            },
            setValue: function(value) {
                if (choicesInstance) {
                    choicesInstance.setChoiceByValue(value);
                }
            },
            getValue: function() {
                if (choicesInstance) {
                    return choicesInstance.getValue(true);
                }
                return null;
            }
        };
    };

})(window);
