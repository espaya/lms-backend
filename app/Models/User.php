<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ✅ Required for $user->tokens() and createToken()


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'privacy',
        'role'
    ];


    public function applicationForm()
    {
        return $this->hasOne(EmploymentApplication::class, 'applicant_id');
    }

    public function verification()
    {
        return $this->hasOne(Verification::class, 'applicant_id');
    }

    public function attendanceTardiness()
    {
        return $this->hasOne(AttendanceTardiness::class, 'applicant_id');
    }

    public function confidentialityInformation()
    {
        return $this->hasOne(ConfidentialityInformation::class, 'applicant_id');
    }

    public function criminalHistorySearch()
    {
        return $this->hasOne(CriminalHistorySearch::class, 'applicant_id');
    }

    public function disclaimerWaiverLiability()
    {
        return $this->hasOne(DisclaimerWaiverLiability::class, 'applicant_id');
    }

    public function drugTestingPolicy()
    {
        return $this->hasOne(DrugTestingPolicy::class, 'applicant_id');
    }

    public function employeeAgreement()
    {
        return $this->hasOne(EmployeeAgreement::class, 'applicant_id');
    }

    public function employeeConduct()
    {
        return $this->hasOne(EmployeeConduct::class, 'applicant_id');
    }

    public function employeeDressCode()
    {
        return $this->hasOne(EmployeeDressCode::class, 'applicant_id');
    }

    public function employeeOrientation()
    {
        return $this->hasOne(EmployeeOrientation::class, 'applicant_id');
    }

    public function employeeReferenceCheck()
    {
        return $this->hasOne(EmployeeReferenceCheck::class, 'applicant_id');
    }

    public function employeeSafety()
    {
        return $this->hasOne(EmployeeSafety::class, 'applicant_id');
    }

    public function healthSafetyAgreement()
    {
        return $this->hasOne(HealthSafetyAgreement::class, 'applicant_id');
    }

    public function homeHealthAide()
    {
        return $this->hasOne(HomeHealthAide::class, 'applicant_id');
    }

    public function infectionControl()
    {
        return $this->hasOne(InfectionControlAgreement::class, 'applicant_id');
    }

    public function nonCompeteAgreement()
    {
        return $this->hasOne(NonCompeteAgreement::class, 'applicant_id');
    }

    public function policyProcedure()
    {
        return $this->hasOne(PolicyAndProcedure::class, 'applicant_id');
    }

    public function reporting()
    {
        return $this->hasOne(Reporting::class, 'applicant_id');
    }

    public function sexualHarassment()
    {
        return $this->hasOne(SexualHarassment::class, 'applicant_id');
    }

    public function smoking()
    {
        return $this->hasOne(Smoking::class, 'applicant_id');
    }

    public function swornDisclosure()
    {
        return $this->hasOne(SwornDisclosure::class, 'applicant_id');
    }

    public function universalPrecaution()
    {
        return $this->hasOne(UniversalPrecautions::class, 'applicant_id');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
