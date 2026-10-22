// The admin panel's interactions. Each module looks for its own markup and does
// nothing when the page doesn't have it.
import { deleteButton } from './admin/delete-button';
import { emailTest } from './admin/email-test';
import { inbox } from './admin/inbox';
import { loginForm } from './admin/login-form';
import { statusPicker } from './admin/status-picker';

loginForm();
inbox();
statusPicker();
deleteButton();
emailTest();
