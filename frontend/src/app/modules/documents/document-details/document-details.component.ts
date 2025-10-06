import { Component, Inject, OnInit } from '@angular/core';
import { Document } from '../../../models/document.model';
import { SearchService } from '../../../services/search.service';
import { DocumentService } from '../../../services/document.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-document-details',
  templateUrl: './document-details.component.html',
  styleUrls: ['./document-details.component.scss']
})
export class DocumentDetailsComponent implements OnInit {
  document: Document | null = null;
  isLoading = false;
  error: string | null = null;

  constructor(
    @Inject(MAT_DIALOG_DATA) public data: { documentId: number },
    private dialogRef: MatDialogRef<DocumentDetailsComponent>,
    private documentService: DocumentService,
    private searchService: SearchService
  ) {}

  ngOnInit(): void {
    this.loadDocument();
  }

  loadDocument(): void {
    this.isLoading = true;
    this.error = null;

    this.documentService.getDocument(this.data.documentId).subscribe({
      next: (doc: any) => {
        this.document = doc;
        this.isLoading = false;
      },
      error: (error) => {
        this.error = 'Failed to load document details';
        this.isLoading = false;
      }
    });
  }

  close(): void {
    this.dialogRef.close();
  }

  download(): void {
    if (!this.document) return;

    this.documentService.downloadDocument(this.document.id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = this.document!.original_filename;
        link.click();
        window.URL.revokeObjectURL(url);
      },
      error: (error) => {
        alert('Failed to download document');
      }
    });
  }

  formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  }
}
