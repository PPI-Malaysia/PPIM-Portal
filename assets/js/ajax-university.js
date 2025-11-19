/**
 * AJAX University Search for Choices.js
 * Provides dynamic search functionality for university dropdowns
 *
 * Usage:
 * 1. Include this script after Choices.js library
 * 2. Add a select element with a specific ID
 * 3. Call initUniversitySearch(selectId, options)
 *
 * Example:
 * <select id="university-select" name="university_id">
 *     <option value="">Select University</option>
 * </select>
 *
 * <script>
 * initUniversitySearch('university-select', {
 *     apiUrl: '../assets/php/API/get-university.php',
 *     limit: 100,
 *     searchMinLength: 2
 * });
 * </script>
 */

(function(window) {
    'use strict';

    /**
     * Initialize university search on a select element
     * @param {string} selectId - The ID of the select element
     * @param {Object} options - Configuration options
     * @returns {Object} - Returns the Choices instance and utility methods
     */
    window.initUniversitySearch = function(selectId, options) {
        // Default options
        const defaults = {
            apiUrl: '../assets/php/API/get-university.php',
            limit: 100,
            searchMinLength: 2,
            placeholder: 'Type to search university...',
            noResultsText: 'No universities found',
            noChoicesText: 'Type to search...',
            onLoad: null,
            onError: null,
            debug: false
        };

        // Merge options with defaults
        const config = Object.assign({}, defaults, options);

        // Get the select element
        const selectElement = document.getElementById(selectId);
        if (!selectElement) {
            console.error(`University select element with ID "${selectId}" not found`);
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
                searchResultLimit: 50,
                shouldSort: false,
                removeItemButton: false,
                searchFloor: config.searchMinLength,
                searchChoices: true
            });

            // Load initial universities
            loadUniversities('');

            // Search on input
            selectElement.addEventListener('search', function(event) {
                const searchTerm = event.detail.value;
                if (searchTerm.length >= config.searchMinLength) {
                    loadUniversities(searchTerm);
                }
            });

            if (config.debug) {
                console.log(`✓ University search initialized on #${selectId}`);
            }

        } catch (error) {
            console.error('Error initializing university Choices:', error);
            if (config.onError) config.onError(error);
            return null;
        }

        /**
         * Load universities from API
         * @param {string} searchTerm - Search query
         */
        function loadUniversities(searchTerm = '') {
            const cacheKey = searchTerm.toLowerCase();

            // Check cache first
            if (cache[cacheKey]) {
                updateChoices(cache[cacheKey]);
                return;
            }

            // Build URL
            let url = config.apiUrl;
            if (searchTerm) {
                url += '?search=' + encodeURIComponent(searchTerm);
            } else {
                url += '?limit=' + config.limit;
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
                            console.log(`✓ Loaded ${data.count} universities` + (searchTerm ? ` for "${searchTerm}"` : ''));
                        }

                        // Cache the result
                        cache[cacheKey] = data.data;
                        updateChoices(data.data);

                        // Call onLoad callback
                        if (config.onLoad) {
                            config.onLoad(data.data, searchTerm);
                        }
                    } else {
                        console.error('Failed to load universities:', data.error);
                        if (config.onError) config.onError(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching universities:', error);
                    if (config.onError) config.onError(error);
                });
        }

        /**
         * Update Choices.js dropdown with new data
         * @param {Array} universities - Array of university objects
         */
        function updateChoices(universities) {
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
                        label: 'Select University',
                        placeholder: true,
                        disabled: false
                    }
                ];

                // Add university choices
                universities.forEach(uni => {
                    choices.push({
                        value: uni.university_id,
                        label: uni.university_name + (uni.postcode && uni.postcode.city ? ' - ' + uni.postcode.city : ''),
                        selected: false,
                        disabled: false,
                        customProperties: {
                            address: uni.address,
                            type: uni.university_type,
                            postcode: uni.postcode
                        }
                    });
                });

                choicesInstance.setChoices(choices, 'value', 'label', true);
            } catch (error) {
                console.error('Error updating university choices:', error);
                if (config.onError) config.onError(error);
            }
        }

        // Return public API
        return {
            instance: choicesInstance,
            reload: function() {
                cache = {};
                loadUniversities('');
            },
            search: function(term) {
                loadUniversities(term);
            },
            clearCache: function() {
                cache = {};
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
