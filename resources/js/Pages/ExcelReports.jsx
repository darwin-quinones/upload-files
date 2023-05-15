import React, { useState } from "react";

export default function Dashboard() {
    const [files, setFiles] = useState('');
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";

    const generateReportOperatorByPeriod = (event) => {
        event.preventDefault();
        const id_year = '2023';
        const id_month = '01';
        const report_code = 1;
        const filename = "Reporte Operadores - Periodo " + id_year + id_month + ".xlsx";

        const data = { id_month: id_month, id_year: id_year, report_code: report_code };
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                // Check if the response was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as a blob
                console.log(response)
                return response.blob();
            })
            .then(blob => {
                console.log(blob)
                // Create a download link for the blob
                const url = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                // Trigger the download link
                link.click();
                // Cleanup the link and object URL
                link.parentNode.removeChild(link);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });

    };

    const generateReportOperatorByDataRange = (event) => {
        event.preventDefault();
        const fecha_inicio = '2022-11-01';
        const fecha_fin = '2023-05-08';
        const report_code = 2;
        const filename = "Reporte Operadores - Rango " + fecha_inicio + " & " + fecha_fin + ".xlsx";
        const data = { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, report_code: report_code }
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                // Check if the response was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as a blob
                console.log(response)
                return response.blob();
            })
            .then(blob => {
                // Create a download link for the blob
                const url = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a')
                link.href = url;
                link.setAttribute('download', filename)
                // trigger the download link
                link.click()
                // Cleanup the link and object URL
                link.parentNode.removeChild(link)
                window.URL.revokeObjectURL(url)
            })
            .catch((error) => {
                console.error(`There was an error downloading file: ${error.message}`)
            })
    };
    const generateReportComercializadorByPeriod = (event) => {
        event.preventDefault();
        const id_year = '2023';
        const id_month = '02';
        const report_code = 3;
        const filename = "Reporte Comercializadores - Periodo " + id_year + id_month + ".xlsx";

        const data = { id_month: id_month, id_year: id_year, report_code: report_code };
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                // Check if the response was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as a blob
                console.log(response)
                return response.blob();
            })
            .then(blob => {
                console.log(blob)
                // Create a download link for the blob
                const url = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                // Trigger the download link
                link.click();
                // Cleanup the link and object URL
                link.parentNode.removeChild(link);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });

    };
    const generateReportComercializadorByDataRange = (event) => {
        event.preventDefault();
        const fecha_inicio = '2023-01-01';
        const fecha_fin = '2023-05-08';
        const report_code = 4;
        const filename = "Reporte comercializadores - Rango " + fecha_inicio + " & " + fecha_fin + ".xlsx";
        const data = { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin, report_code: report_code }
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                // Check if the response was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as a blob
                console.log(response)
                return response.blob();
            })
            .then(blob => {
                // Create a download link for the blob
                const url = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a')
                link.href = url;
                link.setAttribute('download', filename)
                // trigger the download link
                link.click()
                // Cleanup the link and object URL
                link.parentNode.removeChild(link)
                window.URL.revokeObjectURL(url)
            })
            .catch((error) => {
                console.error(`There was an error downloading file: ${error.message}`)
            })
    };

    const generateReportEspecialesByPeriod = (event) => {
        event.preventDefault()
        const id_year = '2023'
        const id_month = '02'
        const report_code = 5
        const filename = "Reporte Cliente Especiales - Periodo " + id_year + id_month + ".xlsx";
        //Reporte Cliente Especiales - Periodo 202301.xlsx
        //Reporte Cliente Especiales - Periodo 202301.xlsx
        //const filename = "Reporte comercializadores - Rango " + fecha_inicio + " & " + fecha_fin + ".xlsx";
        const data = { id_month: id_month, id_year: id_year, report_code: report_code }
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
        })
        .then(response => {
            //check if the response was successful
            if(!response.ok){
                throw new Error('Network response was not ok')
            }
            //parse the response as a blob
            return response.blob()
        })
        .then(blob => {
            // create a download link for the blob
            const url = window.URL.createObjectURL(new Blob([blob]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', filename)
            // trigger the download link
            link.click()
            // cleanup the link and object url
            link.parentNode.removeChild(link)
            window.revokeObjectURL(url)
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation: ', error)
        })
    }
    const generateReportEspecialesByMunicipality = (event) => {
        event.preventDefault()
        console.log('yes')
    }
    const generateReportEspecialesContributor = (event) => {
        event.preventDefault()
        console.log('yes')
    }
    const generateReportEspecialesByRange = (event) => {
        event.preventDefault()
        console.log('yes')
    }


    return (

        <div>
            <h>Aqui podras generar reportes en excel</h> <br />
            <button type="submit" onClick={generateReportOperatorByPeriod}>Generar Reporte operador por periodo</button><br />
            <button type="submit" onClick={generateReportOperatorByDataRange}>Generar Reporte operador por rango</button><br />
            <button type="submit" onClick={generateReportComercializadorByPeriod}>Generar Reporte comercializadores por periodo</button><br />
            <button type="submit" onClick={generateReportComercializadorByDataRange}>Generar Reporte comercializadores por rango</button><br />

            <br></br><hr /><br></br>
            <button type="submit" onClick={ generateReportEspecialesByPeriod }>Generar reporte especiales por periodo</button><br/>
            <button type="submit" onClick={ generateReportEspecialesByMunicipality }>Generar reporte especiales por municipio</button><br/>
            <button type="submit" onClick={ generateReportEspecialesContributor }>Generar reporte especiales por contribuyente</button><br/>
            <button type="submit" onClick={ generateReportEspecialesByRange }>Generar reporte especiales por rango</button><br/>


        </div>

    );
}
