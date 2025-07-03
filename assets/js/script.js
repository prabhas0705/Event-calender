$(document).ready(function() {
    // Function to refresh both calendars
    function refreshCalendars() {
        $('#calendar').fullCalendar('refetchEvents');
        $('#mini-calendar').fullCalendar('refetchEvents');
    }

    // Function to generate year options
    function generateYearOptions() {
        const currentYear = moment().year();
        const years = [];
        // Generate 20 years before and 10 years after current year
        for (let i = currentYear - 20; i <= currentYear + 10; i++) {
            years.push(i);
        }
        return years;
    }

    // Function to update mini calendar date
    function updateMiniCalendarDate() {
        const selectedYear = $('#miniYearSelect').val();
        const selectedMonth = $('#miniMonthSelect').val();
        const newDate = moment().year(selectedYear).month(selectedMonth);
        
        // Update both calendars
        $('#mini-calendar').fullCalendar('gotoDate', newDate);
        $('#calendar').fullCalendar('gotoDate', newDate);
    }

    // Function to show event details
    function showEventDetails(event) {
        $('#eventDetailsTitle').text(event.title);
        $('#eventDetailsTime').text(moment(event.start).format('MMMM D, YYYY h:mm A') + ' - ' + moment(event.end).format('h:mm A'));
        $('#eventDetailsDescription').text(event.description || 'No description available');
        $('#eventDetailsModal').data('eventId', event.id);
        $('#eventDetailsModal').modal('show');
    }

    // Function to format event time for display
    function formatEventTime(event) {
        return moment(event.start).format('MMMM D, YYYY h:mm A') + ' - ' + moment(event.end).format('h:mm A');
    }

    // Function to display search results
    function displaySearchResults(events) {
        var $resultsList = $('#searchResultsList');
        $resultsList.empty();
        
        if (events.length === 0) {
            $resultsList.append('<div class="list-group-item">No events found</div>');
        } else {
            events.forEach(function(event) {
                var $item = $('<div class="list-group-item list-group-item-action">')
                    .css('border-left', '4px solid ' + event.color)
                    .append(
                        $('<div class="d-flex w-100 justify-content-between">')
                            .append($('<h6 class="mb-1">').text(event.title))
                            .append($('<small>').text(event.category))
                    )
                    .append($('<p class="mb-1">').text(event.description || 'No description'))
                    .append($('<small>').text(formatEventTime(event)));
                
                $item.on('click', function() {
                    $('#calendar').fullCalendar('gotoDate', event.start);
                    showEventDetails(event);
                });
                
                $resultsList.append($item);
            });
        }
        
        $('#searchResults').show();
    }

    // Initialize datetime pickers
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        sideBySide: true,
        stepping: 15
    });

    // Initialize the main calendar
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        editable: true,
        eventLimit: true,
        events: 'fetch_events.php',
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            // Clear form
            $('#eventForm')[0].reset();
            $('#eventId').val('');
            $('#startTime').val(moment(start).format('YYYY-MM-DD HH:mm:ss'));
            $('#endTime').val(moment(end).format('YYYY-MM-DD HH:mm:ss'));
            $('#eventModal').modal('show');
        },
        eventClick: function(event) {
            showEventDetails(event);
        },
        eventDrop: function(event) {
            updateEvent(event);
        },
        eventResize: function(event) {
            updateEvent(event);
        },
        eventRender: function(event, element) {
            if (event.is_recurring) {
                element.addClass('recurring-event');
                element.append('<span class="recurring-icon"><i class="fa fa-repeat"></i></span>');
            }
            element.css('background-color', event.color);
            element.css('border-color', event.color);
        },
        dayRender: function(date, cell) {
            if (moment().format('YYYY-MM-DD') === date.format('YYYY-MM-DD')) {
                cell.css('background-color', '#e6f7ff');
                cell.css('border', '2px solid #1890ff');
            }
        },
        datesRender: function(date) {
            // Update mini calendar dropdowns when main calendar changes
            $('#miniYearSelect').val(date.year());
            $('#miniMonthSelect').val(date.month());
        }
    });

    // Create month and year dropdowns for mini calendar
    const monthNames = moment.months();
    const yearOptions = generateYearOptions();
    const currentDate = moment();

    // Create month dropdown HTML
    const monthSelect = $('<select>').attr('id', 'miniMonthSelect').addClass('form-control form-control-sm d-inline-block w-auto');
    monthNames.forEach((month, index) => {
        monthSelect.append($('<option>').val(index).text(month));
    });
    monthSelect.val(currentDate.month());

    // Create year dropdown HTML
    const yearSelect = $('<select>').attr('id', 'miniYearSelect').addClass('form-control form-control-sm d-inline-block w-auto ml-2');
    yearOptions.forEach(year => {
        yearSelect.append($('<option>').val(year).text(year));
    });
    yearSelect.val(currentDate.year());

    // Initialize the mini calendar
    $('#mini-calendar').fullCalendar({
        header: false, // Remove default header
        defaultView: 'month',
        height: 'auto',
        aspectRatio: 1,
        events: {
            url: 'fetch_events.php',
            data: function() {
                return {
                    search: $('#eventSearch').val(),
                    category: $('#categoryFilter').val()
                };
            }
        },
        eventClick: function(event) {
            $('#calendar').fullCalendar('gotoDate', event.start);
            $('#eventModal').modal('show');
            $('#eventId').val(event.id);
            $('#eventTitle').val(event.title);
            $('#eventCategory').val(event.category);
            $('#startTime').val(moment(event.start).format('YYYY-MM-DD HH:mm:ss'));
            $('#endTime').val(moment(event.end).format('YYYY-MM-DD HH:mm:ss'));
        },
        dayClick: function(date) {
            // Switch main calendar to day view and go to clicked date
            $('#calendar').fullCalendar('changeView', 'agendaDay');
            $('#calendar').fullCalendar('gotoDate', date);
            
            // Update mini calendar dropdowns
            $('#miniYearSelect').val(date.year());
            $('#miniMonthSelect').val(date.month());

            // Get events for this day
            var events = $('#calendar').fullCalendar('clientEvents', function(event) {
                return moment(event.start).format('YYYY-MM-DD') === date.format('YYYY-MM-DD');
            });

            // If there are events, show the first one's details
            if (events.length > 0) {
                showEventDetails(events[0]);
            }
        },
        eventRender: function(event, element) {
            element.css('background-color', event.color);
            element.css('border-color', event.color);
        },
        dayRender: function(date, cell) {
            if (moment().format('YYYY-MM-DD') === date.format('YYYY-MM-DD')) {
                cell.css('background-color', '#e6f7ff');
                cell.css('border', '2px solid #1890ff');
            }
        }
    });

    // Add dropdowns to mini calendar header
    const miniCalendarHeader = $('<div>').addClass('text-center mb-2');
    miniCalendarHeader.append(monthSelect).append(yearSelect);
    $('#mini-calendar').before(miniCalendarHeader);

    // Add event listeners for dropdowns
    $('#miniMonthSelect, #miniYearSelect').on('change', function() {
        updateMiniCalendarDate();
    });

    // Synchronize both calendars when navigating
    $('#calendar').on('datesRender', function() {
        var date = $('#calendar').fullCalendar('getDate');
        $('#mini-calendar').fullCalendar('gotoDate', date);
    });

    // Handle recurring event checkbox
    $('#isRecurring').on('change', function() {
        var isRecurring = this.checked;
        $('#singleEventOptions').toggle(!isRecurring);
        $('#recurrenceOptions').toggle(isRecurring);
        
        if (isRecurring) {
            // Copy start time to recurrence start time if it exists
            if ($('#startTime').val()) {
                $('#recurrenceStartTime').val($('#startTime').val());
            } else {
                // Set default start time to next 15-minute interval
                var now = moment();
                var defaultStart = now.minutes(Math.ceil(now.minutes() / 15) * 15);
                $('#recurrenceStartTime').val(defaultStart.format('YYYY-MM-DD HH:mm'));
            }
            
            // Set default end date to 1 year from start date
            var startDate = moment($('#recurrenceStartTime').val());
            $('#recurrenceEndDate').val(startDate.add(1, 'year').format('YYYY-MM-DD'));
        }
    });

    // Handle event form submission
    $('#eventForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData();
        formData.append('title', $('#eventTitle').val());
        formData.append('category', $('#eventCategory').val());
        formData.append('description', $('#eventDescription').val());
        formData.append('start', $('#startTime').val());
        formData.append('end', $('#endTime').val());
        
        if ($('#eventId').val()) {
            formData.append('id', $('#eventId').val());
        }

        var url = $('#eventId').val() ? 'edit_event.php' : 'add_event.php';

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.status === 'success') {
                        $('#eventModal').modal('hide');
                        refreshCalendars();
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error occurred'));
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Error processing server response');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving event: ' + error);
            }
        });
    });

    // When opening the event modal for a new event
    $('#eventModal').on('show.bs.modal', function() {
        // Only set default times for new events (when eventId is empty)
        if (!$('#eventId').val()) {
            var now = moment();
            var defaultStart = now.minutes(Math.ceil(now.minutes() / 15) * 15);
            var defaultEnd = defaultStart.clone().add(1, 'hours');
            
            $('#startTime').val(defaultStart.format('YYYY-MM-DD HH:mm'));
            $('#endTime').val(defaultEnd.format('YYYY-MM-DD HH:mm'));
            $('#recurrenceStartTime').val(defaultStart.format('YYYY-MM-DD HH:mm'));
        }
    });

    // When start time changes, update end time if it's before start time
    $('#startTime').on('dp.change', function(e) {
        var startTime = moment(e.date);
        var endTime = moment($('#endTime').val());
        
        if (endTime.isBefore(startTime)) {
            $('#endTime').val(startTime.add(1, 'hours').format('YYYY-MM-DD HH:mm'));
        }
    });

    // Handle edit from details modal
    $('#editEventFromDetails').on('click', function() {
        var eventId = $('#eventDetailsModal').data('eventId');
        var event = $('#calendar').fullCalendar('clientEvents', eventId)[0];
        
        if (event) {
            $('#eventId').val(event.id);
            $('#eventTitle').val(event.title);
            $('#eventCategory').val(event.category);
            $('#eventDescription').val(event.description || '');
            $('#startTime').val(moment(event.start).format('YYYY-MM-DD HH:mm:ss'));
            $('#endTime').val(moment(event.end).format('YYYY-MM-DD HH:mm:ss'));
            
            $('#eventDetailsModal').modal('hide');
            $('#eventModal').modal('show');
        }
    });

    // Handle delete from details modal
    $('#deleteEventFromDetails').on('click', function() {
        var eventId = $('#eventDetailsModal').data('eventId');
        if (confirm('Are you sure you want to delete this event?')) {
            var formData = new FormData();
            formData.append('id', eventId);
            
            $.ajax({
                url: 'delete_event.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.status === 'success') {
                            $('#eventDetailsModal').modal('hide');
                            refreshCalendars();
                        } else {
                            alert('Error: ' + (result.message || 'Unknown error occurred'));
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error processing server response');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error deleting event: ' + error);
                }
            });
        }
    });

    // Handle search input
    var searchTimeout;
    $('#eventSearch').on('input', function() {
        clearTimeout(searchTimeout);
        var searchTerm = $(this).val().trim();
        
        if (searchTerm.length >= 2) {
            searchTimeout = setTimeout(function() {
                var events = $('#calendar').fullCalendar('clientEvents', function(event) {
                    var searchLower = searchTerm.toLowerCase();
                    return event.title.toLowerCase().includes(searchLower) || 
                           (event.description && event.description.toLowerCase().includes(searchLower));
                });
                
                displaySearchResults(events);
            }, 300);
        } else if (searchTerm.length === 0) {
            $('#searchResults').hide();
        }
    });

    // Handle clear search button
    $('#clearSearch').on('click', function() {
        $('#eventSearch').val('');
        $('#searchResults').hide();
    });

    // Handle category filter
    $('#categoryFilter').on('change', function() {
        refreshCalendars();
    });

    // Function to update event
    function updateEvent(event) {
        $.ajax({
            url: 'edit_event.php',
            type: 'POST',
            data: {
                id: event.id,
                title: event.title,
                category: event.category,
                description: event.description,
                start: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
                end: moment(event.end).format('YYYY-MM-DD HH:mm:ss')
            },
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        refreshCalendars();
                    } else {
                        alert('Error: ' + result.message);
                        refreshCalendars();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    refreshCalendars();
                }
            },
            error: function(xhr, status, error) {
                alert('Error updating event: ' + error);
                refreshCalendars();
            }
        });
    }
});
