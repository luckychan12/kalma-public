package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;

import android.app.DatePickerDialog;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;

import com.kalma.API_Interaction.APICaller;
import com.kalma.R;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Locale;

public class SignUpActivity extends AppCompatActivity {
    EditText txtFirstName, txtLastName, txtPassword, txtEmail, txtDOB;
    final Calendar myCalendar = Calendar.getInstance();
    Button signUp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sign_up);
        txtFirstName = findViewById(R.id.txtFirstName);
        txtLastName = findViewById(R.id.txtLastName);
        txtPassword = findViewById(R.id.txtPassword);
        txtEmail = findViewById(R.id.txtEmail);
        txtDOB = findViewById(R.id.txtDOB);
        signUp = findViewById(R.id.btnSignUp);


        final DatePickerDialog.OnDateSetListener date = new DatePickerDialog.OnDateSetListener() {
            @Override
            public void onDateSet(DatePicker view, int year, int monthOfYear,
                                  int dayOfMonth) {
                myCalendar.set(Calendar.YEAR, year);
                myCalendar.set(Calendar.MONTH, monthOfYear);
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy", Locale.UK);
                txtDOB.setText(sdf.format(myCalendar.getTime()));

            }
        };

        signUp.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String firstName = txtFirstName.getText().toString();
                String lastNAme = txtLastName.getText().toString();
                String password = txtPassword.getText().toString();
                String email = txtEmail.getText().toString();
                System.out.println(txtDOB.getText());
            }
        });

        txtDOB.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                DatePickerDialog datePicker = new DatePickerDialog(SignUpActivity.this, android.R.style.Theme_Holo_Dialog, date, 1990, 0, 0);
                datePicker.show();
            }
        });
    }

    private void signup(String firstName, String lastName, String password, String email, int DOB) {
        APICaller apiCaller = new APICaller(getApplicationContext());
    }
}
