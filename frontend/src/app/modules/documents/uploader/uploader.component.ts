import { Component, EventEmitter, Output } from '@angular/core';
import { DocumentService } from '../../../services/document.service';

@Component({
  selector: 'app-uploader',
  templateUrl: './uploader.component.html',
  styleUrls: ['./uploader.component.scss']
})
export class UploaderComponent {
  @Output() uploadComplete = new EventEmitter<void>();
  
  isDragging = false;
  isUploading = false;
  uploadError: string | null = null;
  uploadSuccess: string | null = null;
  selectedFile: File | null = null;

  constructor(private documentService: DocumentService) {}

  onDragOver(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.isDragging = true;
  }

  onDragLeave(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.isDragging = false;
  }

  onDrop(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.isDragging = false;

    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
      this.handleFile(files[0]);
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.handleFile(input.files[0]);
    }
  }

  private handleFile(file: File): void {
    this.selectedFile = file;
    this.uploadError = null;
    this.uploadSuccess = null;
  }

  uploadFile(): void {
    if (!this.selectedFile) {
      return;
    }

    this.isUploading = true;
    this.uploadError = null;
    this.uploadSuccess = null;

    this.documentService.uploadDocument(this.selectedFile).subscribe({
      next: (response) => {
        this.isUploading = false;
        this.uploadSuccess = `File "${this.selectedFile!.name}" uploaded successfully!`;
        this.selectedFile = null;
        this.uploadComplete.emit();
        
        setTimeout(() => {
          this.uploadSuccess = null;
        }, 5000);
      },
      error: (error) => {
        this.isUploading = false;
        this.uploadError = error.error?.error || 'Upload failed. Please try again.';
      }
    });
  }

  clearFile(): void {
    this.selectedFile = null;
    this.uploadError = null;
    this.uploadSuccess = null;
  }
}
