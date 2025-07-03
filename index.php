<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Set the current date if not specified
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get events for the current date
$events = getEvents($date);
$recurringEvents = getRecurringEvents($date);
$allEvents = array_merge($events, $recurringEvents);

include 'templates/header.php';
include 'templates/navbar.php';

?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Mini Calendar Column -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mini Calendar</h5>
                </div>
                <div class="card-body">
                    <div id="mini-calendar"></div>
                </div>
            </div>
            
            <!-- Search and Filter Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Search & Filter</h5>
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="form-group">
                        <label for="eventSearch">Search Events</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="eventSearch" placeholder="Enter keywords...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="form-group">
                        <label for="categoryFilter">Filter by Category</label>
                        <select class="form-control" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php
                            $categories = getCategories();
                            foreach ($categories as $category) {
                                echo '<option value="' . $category['name'] . '">' . $category['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-3" style="display: none;">
                        <h6>Search Results</h6>
                        <div class="list-group" id="searchResultsList">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Calendar Column -->
        <div class="col-md-9">
            <h1>Event Calendar</h1>
            <div class="calendar-actions">
                <a href="add_event.php" class="btn btn-primary">Add Event</a>
                <a href="add_recurring_event.php" class="btn btn-secondary">Add Recurring Event</a>
            </div>
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal for adding/editing events -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Add/Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" id="eventId">
                    <div class="form-group">
                        <label for="eventTitle">Event Title</label>
                        <input type="text" class="form-control" id="eventTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="eventCategory">Category</label>
                        <select class="form-control" id="eventCategory" required>
                            <?php
                            foreach ($categories as $category) {
                                echo '<option value="' . $category['name'] . '">' . $category['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventDescription">Description</label>
                        <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                    </div>
                    
                    <div id="singleEventOptions">
                        <div class="form-group">
                            <label for="startTime">Start Time</label>
                            <input type="text" class="form-control datetimepicker" id="startTime" required>
                        </div>
                        <div class="form-group">
                            <label for="endTime">End Time</label>
                            <input type="text" class="form-control datetimepicker" id="endTime" required>
                        </div>
                    </div>
                    <div id="recurrenceOptions" style="display: none;">
                        <div class="form-group">
                            <label for="recurrenceStartTime">Start Time</label>
                            <input type="text" class="form-control datetimepicker" id="recurrenceStartTime" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (hours)</label>
                            <input type="number" class="form-control" id="eventDuration" min="0.5" step="0.5" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Repeat</label>
                            <select class="form-control" id="recurrencePattern">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="text" class="form-control datetimepicker" id="recurrenceEndDate">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" role="dialog" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6 id="eventDetailsTitle"></h6>
                <p><strong>Time:</strong> <span id="eventDetailsTime"></span></p>
                <p><strong>Description:</strong> <span id="eventDetailsDescription"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEventFromDetails">Delete Event</button>
                <button type="button" class="btn btn-primary" id="editEventFromDetails">Edit Event</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
