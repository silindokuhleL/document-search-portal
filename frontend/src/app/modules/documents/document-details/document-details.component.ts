import { Component, inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { Document } from '../../../models/document.model';
import { DocumentService } from '../../../services/document.service';

@Component({
  selector: 'app-document-details',
  templateUrl: './document-details.component.html',
  styleUrls: ['./document-details.component.scss']
})
export class DocumentDetailsComponent implements OnInit {
  document: Document | null = null;
  isLoading = false;
  error: string | null = null;

  public data: { documentId: number } = inject(MAT_DIALOG_DATA);
  private readonly dialogRef = inject(MatDialogRef<DocumentDetailsComponent>);
  private readonly documentService = inject(DocumentService);

  ngOnInit(): void {
    this.loadDocument();
  }

  loadDocument(): void {
    this.isLoading = true;
    this.error = null;

    this.documentService.getDocument(this.data.documentId).subscribe({
      next: (doc) => {
        this.document = doc;
        this.isLoading = false;
      },
      error: () => {
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
      error: () => {
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
