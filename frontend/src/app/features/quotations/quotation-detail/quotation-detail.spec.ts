import { ComponentFixture, TestBed } from '@angular/core/testing';

import { QuotationDetail } from './quotation-detail';

describe('QuotationDetail', () => {
  let component: QuotationDetail;
  let fixture: ComponentFixture<QuotationDetail>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [QuotationDetail],
    }).compileComponents();

    fixture = TestBed.createComponent(QuotationDetail);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
