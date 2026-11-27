import { initContactForm } from './contact-form.js';
import { createContactList } from './contact-list.js';

const FIRST_PAGE = 1;

const contactList = createContactList();

initContactForm({
  onContactCreated: contact => {
    contactList.load(FIRST_PAGE, contact.id);
    bootstrap.Toast.getOrCreateInstance(document.getElementById('success-toast')).show();
  },
});

contactList.load(FIRST_PAGE);
