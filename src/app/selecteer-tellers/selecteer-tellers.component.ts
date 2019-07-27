import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-selecteer-tellers',
  templateUrl: './selecteer-tellers.component.html',
  styleUrls: ['./selecteer-tellers.component.scss']
})
export class SelecteerTellersComponent implements OnInit {
  static wedstrijd: any;
  static tijd: string;

  tellersOptiesLoading: boolean;
  spelendeTeams = [];
  overigeTeams = [];
  errorMessage: string;
  wedstrijd: any;
  teams: string;
  tijd: string;

  constructor(
    public modal: NgbActiveModal,
    private requestService: RequestService
  ) {}

  ngOnInit() {
    this.wedstrijd = SelecteerTellersComponent.wedstrijd;
    this.teams = this.wedstrijd.teams;
    this.tijd = SelecteerTellersComponent.tijd;
    this.getTelTeams(this.wedstrijd.id);
  }

  getTelTeams(matchId) {
    this.tellersOptiesLoading = true;

    this.requestService.GetTelTeams(matchId).subscribe(
      zaalwachtopties => {
        this.spelendeTeams = zaalwachtopties.spelendeTeams;
        this.overigeTeams = zaalwachtopties.overigeTeams;
        this.tellersOptiesLoading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error;
          this.tellersOptiesLoading = false;
        }
      }
    );
  }

  UpdateTellers(tellers) {
    this.requestService
      .UpdateTellers(this.wedstrijd.id, tellers)
      .subscribe(() => this.modal.close(tellers));
  }
}
