import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

const MINIMUM_TRAVELER_AGE = 18;
const MAXIMUM_TRAVELER_AGE = 70;

export interface TravelerAgeValidationError {
  readonly index: number;
  readonly age: number | null;
  readonly reason: 'invalid-date' | 'born-after-trip-start' | 'underage' | 'overage';
}

export const quotationDatesValidator: ValidatorFn = (
  control: AbstractControl,
): ValidationErrors | null => {
  const startDateValue = control.get('start_date')?.value;

  const endDateValue = control.get('end_date')?.value;

  if (
    typeof startDateValue !== 'string' ||
    typeof endDateValue !== 'string' ||
    startDateValue === '' ||
    endDateValue === ''
  ) {
    return null;
  }

  const startDate = parseIsoDate(startDateValue);
  const endDate = parseIsoDate(endDateValue);

  if (startDate === null || endDate === null) {
    return null;
  }

  if (endDate.getTime() < startDate.getTime()) {
    return {
      endDateBeforeStartDate: true,
    };
  }

  return null;
};

export const travelerAgesValidator: ValidatorFn = (
  control: AbstractControl,
): ValidationErrors | null => {
  const startDateValue = control.get('start_date')?.value;

  const travelers = control.get('travelers')?.value;

  if (typeof startDateValue !== 'string' || startDateValue === '' || !Array.isArray(travelers)) {
    return null;
  }

  const startDate = parseIsoDate(startDateValue);

  if (startDate === null) {
    return null;
  }

  const invalidTravelers: TravelerAgeValidationError[] = [];

  travelers.forEach((traveler: unknown, index: number): void => {
    if (typeof traveler !== 'object' || traveler === null) {
      return;
    }

    const dateOfBirth = (
      traveler as {
        date_of_birth?: unknown;
      }
    ).date_of_birth;

    if (typeof dateOfBirth !== 'string' || dateOfBirth === '') {
      return;
    }

    const birthDate = parseIsoDate(dateOfBirth);

    if (birthDate === null) {
      invalidTravelers.push({
        index,
        age: null,
        reason: 'invalid-date',
      });

      return;
    }

    if (birthDate.getTime() > startDate.getTime()) {
      invalidTravelers.push({
        index,
        age: null,
        reason: 'born-after-trip-start',
      });

      return;
    }

    const age = calculateAge(birthDate, startDate);

    if (age < MINIMUM_TRAVELER_AGE) {
      invalidTravelers.push({
        index,
        age,
        reason: 'underage',
      });

      return;
    }

    if (age > MAXIMUM_TRAVELER_AGE) {
      invalidTravelers.push({
        index,
        age,
        reason: 'overage',
      });
    }
  });

  if (invalidTravelers.length === 0) {
    return null;
  }

  return {
    invalidTravelerAges: invalidTravelers,
  };
};

export function minimumArrayLength(minimumLength: number): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const value: unknown = control.value;

    if (!Array.isArray(value) || value.length < minimumLength) {
      return {
        minimumArrayLength: {
          requiredLength: minimumLength,
          actualLength: Array.isArray(value) ? value.length : 0,
        },
      };
    }

    return null;
  };
}

export function calculateAge(dateOfBirth: Date, referenceDate: Date): number {
  let age = referenceDate.getUTCFullYear() - dateOfBirth.getUTCFullYear();

  const monthDifference = referenceDate.getUTCMonth() - dateOfBirth.getUTCMonth();

  const birthdayHasNotOccurred =
    monthDifference < 0 ||
    (monthDifference === 0 && referenceDate.getUTCDate() < dateOfBirth.getUTCDate());

  if (birthdayHasNotOccurred) {
    age--;
  }

  return age;
}

export function parseIsoDate(value: string): Date | null {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

  if (match === null) {
    return null;
  }

  const year = Number(match[1]);
  const month = Number(match[2]);
  const day = Number(match[3]);

  const date = new Date(Date.UTC(year, month - 1, day));

  const isValid =
    date.getUTCFullYear() === year && date.getUTCMonth() === month - 1 && date.getUTCDate() === day;

  return isValid ? date : null;
}
