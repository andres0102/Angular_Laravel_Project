/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService, ApiService } from '@app/@core/services';
import { ChangeEvent } from '@ckeditor/ckeditor5-angular/ckeditor.component';
import { FormBuilder, FormGroup, FormArray } from '@angular/forms';
import * as BalloonEditor from '@ckeditor/ckeditor5-build-balloon';


@Component({
  selector: 'app-notices',
  templateUrl: './notices.component.html',
  styleUrls: ['./notices.component.scss']
})
export class NoticesComponent {
  notice_selected = null;
  notices_loaded: boolean = false;
  notices_current_page: number = 0;
  notices_total_pages: number = 100;
  public Editor = BalloonEditor;

  commentsProcessing = {};
  commentsForm: FormGroup = this.formBuilder.group({
    notice_list: this.formBuilder.array([])
  });
  get notice_list(): FormArray { return <FormArray>this.commentsForm.controls.notice_list; }

  commentDefault(notice, author_uuid): FormGroup {
    return this.formBuilder.group({
      notice_uuid: notice.uuid,
      author_uuid: author_uuid,
      comment: '',
      notice: notice
    });
  }

  addCommentControl(notice): void {
    this.notice_list.push(this.commentDefault(notice, this.authService.user.uuid));
  }

  init() {
    this.getNotices(this.notice_selected);
  }

  getNotices(notice_uuid = null) {
    if (notice_uuid) {
      // this.apiService.post('dashboard/notices/' + notice_uuid).subscribe((result) => {
      //   // console.log('Notices', result);
      //   if (!result.error && result.data.length > 0) {
      //     this.notices = result.data;
      //     this.notices_loaded = true;
      //     console.log(this.notices);
      //   }
      // });
    } else {
      if (this.notices_current_page < this.notices_total_pages) {
        this.notices_current_page++;
        this.apiService.post('dashboard/notices?page=' + this.notices_current_page).subscribe((result) => {
          // console.log('Notices', result);
          if (!result.error && result.data.length > 0) {
            result.data.forEach(notice => this.addCommentControl(notice));
            this.notices_current_page = result['page_info']['current_page'];
            this.notices_total_pages = result['page_info']['total_pages'];
            this.notices_loaded = true;
          }
        });
      }
    }
  }

  resetComment(rowIndex) {
    if (!this.commentsProcessing[rowIndex]) {
      let controls = this.notice_list.controls[rowIndex];
      let input = controls.get('comment');
      input.reset();
    }
  }

  submitComment(rowIndex) {
    if (!this.commentsProcessing[rowIndex]) {
      this.commentsProcessing[rowIndex] = true;
      let controls = this.notice_list.controls[rowIndex];
      let input = controls.get('comment');
      console.log(controls.value);
      // input.reset();
    }
  }

  constructor(
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private router: Router,
    public authService: AuthService,
    private apiService: ApiService) {
      this.notice_selected = this.route.snapshot.params.notice_uuid;

      this.init();
  }



  public onChange({editor}: ChangeEvent) {
    const data = editor.getData();
    console.log( data );
  }
}
