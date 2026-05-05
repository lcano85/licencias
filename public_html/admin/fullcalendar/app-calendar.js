class CalendarSchedule {

     constructor() {
          this.body = document.body;
          this.modal = new bootstrap.Modal(document.getElementById('event-modal'), { backdrop: 'static' });
          this.calendar = document.getElementById('calendar');
          this.formEvent = document.getElementById('forms-event');
          this.btnNewEvent = document.getElementById('btn-new-event');
          this.btnDeleteEvent = document.getElementById('btn-delete-event');
          this.btnSaveEvent = document.getElementById('btn-save-event');
          this.modalTitle = document.getElementById('modal-title');
          this.calendarObj = null;
          this.selectedEvent = null;
          this.newEventData = null;
          this.isExternalEvent = false; // flag to track events created by external drop
     }

     trans(key) {
          if (window.codexTranslateValue) {
               return window.codexTranslateValue(key);
          }

          return key;
     }

     getClassByCategory(category) {
          const classes = {
               1: 'bg-primary',
               2: 'bg-info',
               3: 'bg-success',
               4: 'bg-warning'
          };

          return classes[Number(category)] || 'bg-secondary';
     }

     normalizeServerEvent(event, formData) {
          const category = event?.category || event?.type || formData?.get('category');

          return {
               id: event?.id ? String(event.id) : undefined,
               title: event?.title || event?.schedule_title || formData?.get('title') || '',
               start: event?.start || formData?.get('due_date') || null,
               end: event?.end || null,
               location: event?.location || formData?.get('location') || '',
               description: event?.description || formData?.get('description') || '',
               guests: event?.guests || formData?.getAll('guests[]') || [],
               className: event?.className || this.getClassByCategory(category),
               category: category
          };
     }

     upsertCalendarEvent(eventData) {
          if (!eventData?.id) {
               return;
          }

          const existingEvent = this.calendarObj.getEventById(eventData.id);

          if (existingEvent) {
               existingEvent.setProp('title', eventData.title);
               existingEvent.setProp('classNames', [eventData.className]);
               existingEvent.setStart(eventData.start);
               existingEvent.setEnd(eventData.end);
               existingEvent.setExtendedProp('location', eventData.location);
               existingEvent.setExtendedProp('description', eventData.description);
               existingEvent.setExtendedProp('guests', eventData.guests);
               existingEvent.setExtendedProp('category', eventData.category);
               return;
          }

          this.calendarObj.addEvent(eventData);
     }

     refreshCalendar() {
          this.calendarObj.getEvents().forEach((event) => {
               if (!event.source) {
                    event.remove();
               }
          });
          this.calendarObj.refetchEvents();
          this.calendarObj.render();
     }

     onEventClick(info) {
          this.formEvent?.reset();
          this.formEvent?.classList.remove('was-validated');
          this.newEventData = null;
          this.btnDeleteEvent.style.display = "block";
          this.modalTitle.text = this.trans('Edit Schedule');
          this.selectedEvent = info.event;

          document.getElementById('event-title').value = this.selectedEvent.title || '';
          
          // Date & Time
          if (this.selectedEvent.start) {
              const dt = this.selectedEvent.start;
              const year   = dt.getFullYear();
              const month  = String(dt.getMonth() + 1).padStart(2, '0');
              const day    = String(dt.getDate()).padStart(2, '0');
              const hours  = String(dt.getHours()).padStart(2, '0');
              const minutes = String(dt.getMinutes()).padStart(2, '0');

              // Format => Y-m-d H:i
              const formatted = `${year}-${month}-${day} ${hours}:${minutes}`;
              document.getElementById('due_date').value = formatted;
          }

          // Other fields
          document.getElementById('location').value = this.selectedEvent.extendedProps.location || '';
          document.getElementById('description').value = this.selectedEvent.extendedProps.description || '';

          const categorySelect = document.getElementById('event-category');
          if (this.selectedEvent.extendedProps.category) {
               categorySelect.value = this.selectedEvent.extendedProps.category;
          }

          // ✅ Store guests in temp variable so Choices can pick up later
          const guests = this.selectedEvent.extendedProps.guests;
          if (guests) {
              window._selectedGuests = Array.isArray(guests)
                  ? guests.map(String)
                  : [String(guests)];
          } else {
              window._selectedGuests = [];
          }
          this.modal.show();
     }

     onSelect(info) {
          this.formEvent?.reset();
          this.formEvent?.classList.remove('was-validated');
          this.selectedEvent = null;
          this.newEventData = info;
          this.btnDeleteEvent.style.display = "none";
          this.modalTitle.text = this.trans('Add New Schedule');
          this.calendarObj.unselect();

          if (info?.draggedEl) {
               const typeText = info.draggedEl.innerText.trim();
               const select = document.getElementById('event-category');
               for (let option of select.options) {
                    if ((option.text || '').toLowerCase() === (typeText || '').toLowerCase()) {
                         option.selected = true;
                         break;
                    }
               }
          }
          // Reset guest selection when adding new
          window._selectedGuests = [];

          this.modal.show();

          if (info?.date) {
              const dt = info.date;

              const year = dt.getFullYear();
              const month = String(dt.getMonth() + 1).padStart(2, '0');
              const day = String(dt.getDate()).padStart(2, '0');

              // If allDay → default time
              const hours = info.allDay ? '09' : String(dt.getHours()).padStart(2, '0');
              const minutes = info.allDay ? '00' : String(dt.getMinutes()).padStart(2, '0');

              const formatted = `${year}-${month}-${day} ${hours}:${minutes}`;

              const dueInput = document.getElementById('due_date');
              dueInput.value = formatted;

              // Sync flatpickr UI
              if (dueInput._flatpickr) {
                  dueInput._flatpickr.setDate(formatted, true);
              }
          }
     }

     onEventReceive(info) {
          this.formEvent?.reset();
          this.formEvent?.classList.remove('was-validated');

          this.selectedEvent = info.event;
          this.newEventData = null;
          this.isExternalEvent = true;

          this.btnDeleteEvent.style.display = "block";
          this.modalTitle.text = this.trans('Add New Schedule');

          document.getElementById('event-title').value = this.selectedEvent.title || '';

          const categorySelect = document.getElementById('event-category');
          const className = (this.selectedEvent.classNames && this.selectedEvent.classNames[0]) || this.selectedEvent.className || '';
          let matched = false;
          for (let opt of categorySelect.options) {
               if (opt.getAttribute('data-ecolor') === className) {
                    opt.selected = true;
                    matched = true;
                    break;
               }
          }
          if (!matched && info?.draggedEl) {
               const typeText = info.draggedEl.innerText.trim();
               for (let opt of categorySelect.options) {
                    if ((opt.text || '').toLowerCase() === (typeText || '').toLowerCase()) {
                         opt.selected = true;
                         break;
                    }
               }
          }
          // Reset guests
          window._selectedGuests = [];

          this.modal.show();

          if (!info.event.start) return;
          const dt = info.event.start;

          const year = dt.getFullYear();
          const month = String(dt.getMonth() + 1).padStart(2, '0');
          const day = String(dt.getDate()).padStart(2, '0');

          let hours, minutes;
          if (info.event.allDay) {
              hours = '09';
              minutes = '00';
          } else {
              hours = String(dt.getHours()).padStart(2, '0');
              minutes = String(dt.getMinutes()).padStart(2, '0');
              if (hours === '00' && minutes === '00') {
                  hours = '09';
                  minutes = '00';
              }
          }

          const formatted = `${year}-${month}-${day} ${hours}:${minutes}`;
          const dueInput = document.getElementById('due_date');
          if (!dueInput) return;

          dueInput.value = formatted;
          if (dueInput._flatpickr) {
              dueInput._flatpickr.setDate(formatted, true);
          }
     }

     init() {
          const self = this;
          const externalEventContainerEl = document.getElementById('external-events');

          new FullCalendar.Draggable(externalEventContainerEl, {
               itemSelector: '.external-event',
               eventData: function (eventEl) {
                    return {
                         title: "",
                         className: eventEl.getAttribute('data-class'),
                         extendedProps: {
                              categoryText: eventEl.innerText.trim()
                         }
                    };
               }
          });

          self.calendarObj = new FullCalendar.Calendar(self.calendar, {
              locale: window.codexLocale || 'en',
              slotDuration: '00:30:00',
              slotMinTime: '07:00:00',
              slotMaxTime: '19:00:00',
              themeSystem: 'bootstrap',
              bootstrapFontAwesome: false,
              buttonText: window.codexCalendarLocale?.buttonText || {},
              initialView: 'dayGridMonth',
              handleWindowResize: true,
              height: window.innerHeight - 200,
              headerToolbar: {
                  left: 'prev,next today',
                  center: 'title',
                  right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
              },
              events: {
                  url: "/calendar/events",
                  method: "GET",
                  extraParams: function () {
                      return {
                          _: Date.now()
                      };
                  },
                  failure: function () {
                      Swal.fire({
                           icon: 'error',
                           title: self.trans('Error'),
                           text: self.trans('There was an error while fetching events!')
                      });
                  }
              },
              editable: true,
              droppable: true,
              selectable: true,
              dateClick: function (info) {
                  self.onSelect(info);
              },
              eventClick: function (info) {
                  self.onEventClick(info);
              },
              eventReceive: function (info) {
                  self.onEventReceive(info);
              }
          });

          self.calendarObj.render();

          self.btnNewEvent.addEventListener('click', function (e) {
               self.onSelect({ date: new Date(), allDay: true });
          });

          self.formEvent?.addEventListener('submit', function (e) {
              e.preventDefault();
              const form = self.formEvent;

              if (form.checkValidity()) {
                  const formData = new FormData(form);

                  let url, method;
                  if (self.selectedEvent && self.selectedEvent.id) {
                      url = `/calendar/${self.selectedEvent.id}/update`;
                      method = "POST";
                  } else {
                      url = "/calendar/store";
                      method = "POST";
                  }

                  $.ajax({
                      url: url,
                      type: method,
                      data: formData,
                      processData: false,
                      contentType: false,
                      headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                      },
                      success: function (response) {
                          if (response.status === 'success') {
                              const savedEvent = self.normalizeServerEvent(response.event, formData);

                              if (self.selectedEvent && self.selectedEvent.id) {
                                   // Event was updated
                                   self.upsertCalendarEvent(savedEvent);
                                   Swal.fire({
                                        icon: 'success',
                                        title: self.trans('Updated!'),
                                        text: self.trans('Schedule has been updated successfully.'),
                                        timer: 2000,
                                        showConfirmButton: false
                                   });
                              } else {
                                   // Event was created
                                   if (self.isExternalEvent && self.selectedEvent) {
                                        try {
                                             self.selectedEvent.remove();
                                        } catch (err) {
                                             console.log('Could not remove external event');
                                        }
                                   }
                                   Swal.fire({
                                        icon: 'success',
                                        title: self.trans('Added!'),
                                        text: self.trans('Schedule has been added successfully.'),
                                        timer: 2000,
                                        showConfirmButton: false
                                   });
                              }

                              // Refresh calendar to sync with server
                              self.isExternalEvent = false;
                              self.selectedEvent = null;
                              self.refreshCalendar();

                              self.modal.hide();
                          } else {
                              Swal.fire({
                                   icon: 'error',
                                   title: self.trans('Error'),
                                   text: response.message || self.trans('Something went wrong!')
                              });
                          }
                      },
                      error: function (xhr, status, error) {
                          console.error("AJAX Error:", error);
                          Swal.fire({
                               icon: 'error',
                               title: self.trans('Error'),
                               text: self.trans('Something went wrong. Please try again.')
                          });
                      }
                  });
              } else {
                  e.stopPropagation();
                  form.classList.add('was-validated');
              }
          });

          self.btnDeleteEvent.addEventListener('click', function () {
              if (self.selectedEvent && self.selectedEvent.id) {
                  Swal.fire({
                      title: self.trans('Are you sure?'),
                      text: self.trans("You won't be able to revert this!"),
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#3085d6',
                      cancelButtonColor: '#d33',
                      confirmButtonText: self.trans('Yes, Delete It!')
                  }).then((result) => {
                    if (result.isConfirmed) {
                         $.ajax({
                              url: `/calendar/${self.selectedEvent.id}/delete`,
                              type: "DELETE",
                              headers: {
                                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                              },
                              success: function (response) {
                                   if (response.status === 'success') {
                                        Swal.fire({
                                             icon: 'success',
                                             title: self.trans('Deleted!'),
                                             text: self.trans('Schedule has been deleted.'),
                                             timer: 2000,
                                             showConfirmButton: false
                                        });
                                        // Refresh calendar to sync deletion with server
                                        self.calendarObj.refetchEvents();
                                        self.modal.hide();
                                   }
                              }
                         });
                    }
                  });
              }
          });

          const modalEl = document.getElementById('event-modal');
          modalEl.addEventListener('hidden.bs.modal', function () {
               // Clean up external events when modal closes
               if (self.isExternalEvent && self.selectedEvent) {
                    try {
                         self.selectedEvent.remove();
                    } catch (err) {}
                    self.isExternalEvent = false;
               }
               self.selectedEvent = null;
               self.newEventData = null;
          });
     }

}

document.addEventListener('DOMContentLoaded', function (e) {
     new CalendarSchedule().init();
     let guestChoices = null;
     document.getElementById('event-modal').addEventListener('shown.bs.modal', function () {
         const element = document.getElementById('choices-multiple-default');
         if (guestChoices) {
             guestChoices.destroy();
         }
         guestChoices = new Choices(element, {
             removeItemButton: true,
             placeholder: true,
             placeholderValue: window.codexTranslateValue ? window.codexTranslateValue('Select users...') : 'Select users...',
         });

         if (window._selectedGuests && Array.isArray(window._selectedGuests)) {
             guestChoices.removeActiveItems();
             window._selectedGuests.forEach(id => {
                 guestChoices.setChoiceByValue(String(id));
             });
             window._selectedGuests = null;
         }
     });
});
