import { Component } from '@angular/core';
import { FormGroup,  FormControl,  Validators } from '@angular/forms';
import { CalendarService } from './calendar.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  inputForm: FormGroup
  hangoutLink: string;

  constructor(private calendarService: CalendarService) {
    this.createForm();
  }

  createForm() {
    this.inputForm = new FormGroup({
      userA: new FormControl("", [Validators.required,
            Validators.pattern('[a-zA-z0-9_\.]+@[a-zA-Z]+\.[a-zA-Z]+')]),
      userB: new FormControl("",           [Validators.required,
            Validators.pattern('[a-zA-z0-9_\.]+@[a-zA-Z]+\.[a-zA-Z]+')]),
      date: new FormControl("", Validators.required),
      time: new FormControl("", Validators.required)
    });
  }

  createEvent(){
    console.log(this.inputForm.value);

    this.calendarService.getLink(this.inputForm.value).subscribe(
      (response) => {
        console.log(response);
        console.log(response['data']);
        this.hangoutLink = response['data'];
      }
    );

    this.inputForm.reset();
  }
}
